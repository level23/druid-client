<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Queries;

use Mockery;
use tests\TestCase;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Hamcrest\Core\IsInstanceOf;
use Level23\Druid\Queries\TopNQuery;
use Level23\Druid\Queries\ScanQuery;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Queries\SelectQuery;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Queries\GroupByQuery;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\Context\QueryContext;
use Level23\Druid\Limits\LimitInterface;
use Level23\Druid\Queries\QueryInterface;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\Types\OrderByDirection;
use Level23\Druid\Queries\TimeSeriesQuery;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Context\TopNQueryContext;
use Level23\Druid\Context\ScanQueryContext;
use Level23\Druid\Extractions\UpperExtraction;
use Level23\Druid\Responses\TopNQueryResponse;
use Level23\Druid\Responses\ScanQueryResponse;
use Level23\Druid\VirtualColumns\VirtualColumn;
use Level23\Druid\Queries\SegmentMetadataQuery;
use Level23\Druid\Dimensions\DimensionInterface;
use Level23\Druid\Context\GroupByV2QueryContext;
use Level23\Druid\Context\GroupByV1QueryContext;
use Level23\Druid\Responses\SelectQueryResponse;
use Level23\Druid\Extractions\ExtractionBuilder;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Context\TimeSeriesQueryContext;
use Level23\Druid\Responses\GroupByQueryResponse;
use Level23\Druid\Collections\DimensionCollection;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Responses\TimeSeriesQueryResponse;
use Level23\Druid\Collections\VirtualColumnCollection;
use Level23\Druid\HavingFilters\HavingFilterInterface;
use Level23\Druid\Collections\PostAggregationCollection;
use Level23\Druid\Responses\SegmentMetadataQueryResponse;

class QueryBuilderTest extends TestCase
{
    /**
     * @var \Level23\Druid\DruidClient|\Mockery\MockInterface|\Mockery\LegacyMockInterface
     */
    protected $client;

    /**
     * @var \Level23\Druid\Queries\QueryBuilder|\Mockery\MockInterface|\Mockery\LegacyMockInterface
     */
    protected $builder;

    public function setUp(): void
    {
        $this->client  = Mockery::mock(DruidClient::class, [[]]);
        $this->builder = Mockery::mock(QueryBuilder::class, [$this->client, 'dataSourceName']);
        $this->builder->makePartial();
        $this->builder->shouldAllowMockingProtectedMethods();
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testExecute()
    {
        $context = ['foo' => 'bar'];
        $query   = $this->getTimeseriesQueryMock();

        $result = ['result' => 'here'];

        $responseObj = new TimeSeriesQueryResponse([], 'timestamp');

        $this->builder
            ->shouldReceive('buildQuery')
            ->with($context)
            ->once()
            ->andReturn($query);

        $this->client->shouldReceive('executeQuery')
            ->once()
            ->with($query)
            ->andReturn($result);

        $query->shouldReceive('parseResponse')
            ->once()
            ->with($result)
            ->andReturn($responseObj);

        $response = $this->builder->execute($context);

        $this->assertEquals($responseObj, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSelectVirtual()
    {
        Mockery::mock('overload:' . VirtualColumn::class)
            ->shouldReceive('__construct')
            ->with('concat(foo, bar)', 'fooBar', 'string');

        $response = $this->builder->selectVirtual('concat(foo, bar)', 'fooBar');

        $this->assertEquals([
            'type'       => 'default',
            'dimension'  => 'fooBar',
            'outputType' => 'string',
            'outputName' => 'fooBar',
        ], $this->builder->getDimensions()[0]->toArray());

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGranularity()
    {
        $response = $this->builder->granularity('year');
        $this->assertEquals($this->builder, $response);

        $this->assertEquals('year', $this->getProperty($this->builder, 'granularity'));
    }

    public function testToJson()
    {
        $context = ['foo' => 'bar'];
        $query   = $this->getTimeseriesQueryMock();

        $result = ['result' => 'here'];

        $this->builder
            ->shouldReceive('buildQuery')
            ->with($context)
            ->once()
            ->andReturn($query);

        $query->shouldReceive('toArray')
            ->once()
            ->andReturn($result);

        $response = $this->builder->toJson($context);

        $this->assertEquals(json_encode($result, JSON_PRETTY_PRINT), $response);
    }

    public function testToArray()
    {
        $context = ['foo' => 'bar'];
        $query   = $this->getTimeseriesQueryMock();

        $result = ['result' => 'here'];

        $this->builder
            ->shouldReceive('buildQuery')
            ->with($context)
            ->once()
            ->andReturn($query);

        $query->shouldReceive('toArray')
            ->once()
            ->andReturn($result);

        $response = $this->builder->toArray($context);

        $this->assertEquals($result, $response);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testTimeseries()
    {
        $context = ['foo' => 'bar'];
        $query   = $this->getTimeseriesQueryMock();

        $result             = ['event' => ['result' => 'here']];
        $timeSeriesResponse = new TimeSeriesQueryResponse([], 'timestamp');

        $this->builder
            ->shouldReceive('buildTimeSeriesQuery')
            ->with($context)
            ->once()
            ->andReturn($query);

        $this->client
            ->shouldReceive('executeQuery')
            ->with($query)
            ->once()
            ->andReturn($result);

        $query->shouldReceive('parseResponse')
            ->once()
            ->with($result)
            ->andReturn($timeSeriesResponse);

        $response = $this->builder->timeseries($context);

        $this->assertEquals($timeSeriesResponse, $response);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception
     */
    public function testTopN()
    {
        $context = ['foo' => 'bar'];
        $query   = $this->getTopNQueryMock();

        $result       = ['event' => ['result' => 'here']];
        $topNResponse = new TopNQueryResponse([]);

        $this->builder->shouldReceive('buildTopNQuery')
            ->with($context)
            ->once()
            ->andReturn($query);

        $this->client->shouldReceive('executeQuery')
            ->with($query)
            ->once()
            ->andReturn($result);

        $query->shouldReceive('parseResponse')
            ->once()
            ->with($result)
            ->andReturn($topNResponse);

        $response = $this->builder->topN($context);

        $this->assertEquals($topNResponse, $response);
    }

    /**
     * @testWith [null, true, "list"]
     *           [5, false, "compactedList"]
     *
     * @param int|null $rowBatchSize
     * @param bool     $legacy
     * @param string   $resultFormat
     *
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception
     */
    public function testScan(?int $rowBatchSize, bool $legacy, string $resultFormat)
    {
        $context = ['foo' => 'bar'];
        $query   = $this->getScanQueryMock();

        $result       = ['event' => ['result' => 'here']];
        $scanResponse = new ScanQueryResponse([]);

        $this->builder->shouldReceive('buildScanQuery')
            ->with($context, $rowBatchSize, $legacy, $resultFormat)
            ->once()
            ->andReturn($query);

        $this->client->shouldReceive('executeQuery')
            ->with($query)
            ->once()
            ->andReturn($result);

        $query->shouldReceive('parseResponse')
            ->once()
            ->with($result)
            ->andReturn($scanResponse);

        $response = $this->builder->scan($context, $rowBatchSize, $legacy, $resultFormat);

        $this->assertEquals($scanResponse, $response);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception
     */
    public function testSelect()
    {
        $context = ['foo' => 'bar'];
        $query   = $this->getSelectQueryMock();

        $result         = ['event' => ['result' => 'here']];
        $selectResponse = new SelectQueryResponse([]);

        $this->builder->shouldReceive('buildSelectQuery')
            ->with($context)
            ->once()
            ->andReturn($query);

        $this->client->shouldReceive('executeQuery')
            ->with($query)
            ->once()
            ->andReturn($result);

        $query->shouldReceive('parseResponse')
            ->once()
            ->with($result)
            ->andReturn($selectResponse);

        $response = $this->builder->selectQuery($context);

        $this->assertEquals($selectResponse, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testSegmentMetadata()
    {
        $builder = new Mockery\Generator\MockConfigurationBuilder();
        $builder->setInstanceMock(true);
        $builder->setName(SegmentMetadataQuery::class);
        $builder->addTarget(QueryInterface::class);

        $query = Mockery::mock($builder);
        $query->shouldReceive('__construct')->once();

        $result          = ['event' => ['result' => 'here']];
        $segmentResponse = new SegmentMetadataQueryResponse([]);

        $this->client->shouldReceive('executeQuery')
            ->with(new IsInstanceOf(SegmentMetadataQuery::class))
            ->once()
            ->andReturn($result);

        $query->shouldReceive('parseResponse')
            ->once()
            ->with($result)
            ->andReturn($segmentResponse);

        $response = $this->builder->segmentMetadata();

        $this->assertEquals($segmentResponse, $response);
    }

    /**
     * @throws \ReflectionException
     */
    public function testDatasource()
    {
        $response = $this->builder->dataSource('myFavorite');
        $this->assertEquals($this->builder, $response);

        $this->assertEquals('myFavorite', $this->getProperty($this->builder, 'dataSource'));
    }

    /**
     * @testWith [{"__time":"hour"}, 5, "hour", true]
     *           [{"__time":"hour", "name":"name"}, 5, "hour", false]
     *           [{"__time":"hour"}, null, "hour", false]
     *           [{"__time":"hour"}, 999999, "hour", false]
     *           [{"__time":"hour"}, 5, null, false]
     *
     * @param array       $dimensions
     * @param int|null    $limit
     * @param string|null $orderBy
     * @param bool        $expected
     */
    public function testIsTopNQuery(array $dimensions, ?int $limit, ?string $orderBy, bool $expected)
    {
        $this->builder->select($dimensions);

        if ($limit) {
            $this->builder->limit($limit);
        }

        if ($orderBy) {
            $this->builder->orderBy($orderBy, 'asc');
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals($expected, $this->builder->shouldAllowMockingProtectedMethods()->isTopNQuery());
    }

    public function isTimeSeriesQueryDataProvider(): array
    {
        return [
            [['__time' => 'hour'], true],
            [new Dimension('__hour', 'time', 'string', new UpperExtraction()), false],
            [['time' => 'hour'], false],
            [['__time' => 'hour', 'full_name'], false],
        ];
    }

    /**
     * @dataProvider isTimeSeriesQueryDataProvider
     *
     * @param mixed $dimension
     * @param bool  $expected
     */
    public function testIsTimeSeriesQuery($dimension, bool $expected)
    {
        $this->builder->select($dimension);

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals($expected, $this->builder->shouldAllowMockingProtectedMethods()->isTimeSeriesQuery());
    }

    /**
     * @testWith [true, true]
     *           [true, false]
     *           [false, true]
     *           [false, false]
     *
     * @param bool $withIdentifier
     * @param bool $withAggregations
     */
    public function testIsSelectQuery(bool $withIdentifier, bool $withAggregations)
    {
        $expected = ($withIdentifier && !$withAggregations);

        if ($withIdentifier) {
            $this->builder->pagingIdentifier([
                'wikipedia_2015-09-12T00:00:00.000Z_2015-09-13T00:00:00.000Z_2019-09-12T14:15:44.694Z' => 9,
            ]);
        }

        if ($withAggregations) {
            $this->builder->sum('pages');
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals($expected, $this->builder->shouldAllowMockingProtectedMethods()->isSelectQuery());
    }

    /**
     * @testWith [true, true]
     *           [true, false]
     *           [false, true]
     *           [false, false]
     *
     * @param bool $withCorrectDimensions
     * @param bool $withAggregations
     */
    public function testIsScanQuery(bool $withCorrectDimensions, bool $withAggregations)
    {
        $expected = ($withCorrectDimensions && !$withAggregations);

        if ($withCorrectDimensions) {
            $this->builder->select(['channel', 'comment']);
        } else {
            $this->builder->select(['channel' => 'myChannel']);
            $this->builder->lookup('country', 'country_iso');
        }

        if ($withAggregations) {
            $this->builder->sum('pages');
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals($expected, $this->builder->shouldAllowMockingProtectedMethods()->isScanQuery());
    }

    /**
     * @testWith [false, false, true]
     *           [false, true, true]
     *           [true, true, true]
     *           [true, true, false]
     *           [true, false, false]
     *           [true, false, true]
     *           [false, false, false]
     *           [false, true, false]
     *
     * @param bool $onlyDimensions
     * @param bool $alias
     * @param bool $extractions
     */
    public function testIsDimensionsListScanCompliant(bool $onlyDimensions, bool $alias, bool $extractions)
    {
        $expected = true;
        if (!$onlyDimensions) {
            $this->builder->lookup('country', 'country_iso');
            $expected = false;
        }

        if ($alias) {
            $this->builder->select(['channel' => 'myChannel']);
            $expected = false;
        }

        if ($extractions) {
            $this->builder->select('FirstName', 'name', function (ExtractionBuilder $extractionBuilder) {
                $extractionBuilder->lower();
            });
            $expected = false;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals($expected,
            $this->builder->shouldAllowMockingProtectedMethods()->isDimensionsListScanCompliant());
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception
     */
    public function testGroupByV1()
    {
        $context = ['foo' => 'bar'];
        $query   = $this->getGroupByQueryMock();

        $result       = [['event' => ['result' => 'here']]];
        $parsedResult = new GroupByQueryResponse($result);

        $this->builder->shouldReceive('buildGroupByQuery')
            ->with($context, 'v1')
            ->once()
            ->andReturn($query);

        $this->client->shouldReceive('executeQuery')
            ->with($query)
            ->once()
            ->andReturn($result);

        $query->shouldReceive('parseResponse')
            ->once()
            ->with($result)
            ->andReturn($parsedResult);

        $response = $this->builder->groupByV1($context);

        $this->assertEquals($parsedResult, $response);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception
     */
    public function testGroupBy()
    {
        $context = ['foo' => 'bar'];
        $query   = $this->getGroupByQueryMock();

        $result       = [['event' => ['result' => 'here']]];
        $parsedResult = new GroupByQueryResponse($result);

        $this->builder->shouldReceive('buildGroupByQuery')
            ->with($context, 'v2')
            ->once()
            ->andReturn($query);

        $this->client->shouldReceive('executeQuery')
            ->with($query)
            ->once()
            ->andReturn($result);

        $query->shouldReceive('parseResponse')
            ->once()
            ->with($result)
            ->andReturn($parsedResult);

        $response = $this->builder->groupBy($context);

        $this->assertEquals($parsedResult, $response);
    }

    /**
     * @throws \ReflectionException
     */
    public function testPagingIdentifier()
    {
        $identifier = [
            'wikipedia_2015-09-12T00:00:00.000Z_2015-09-13T00:00:00.000Z_2019-09-12T14:15:44.694Z' => 9,
        ];
        $this->builder->pagingIdentifier($identifier);

        $this->assertEquals(
            $identifier,
            $this->getProperty($this->builder, 'pagingIdentifier')
        );
    }

    /**
     * @param bool $isTimeSeries
     * @param bool $isTopNQuery
     * @param bool $isScanQuery
     * @param bool $isSelectQuery
     *
     * @throws \Exception
     * @testWith [true, false, false, false]
     *           [false, true, false, false]
     *           [false, false, true, false]
     *           [false, false, false, true]
     *           [false, false, false, false]
     *
     */
    public function testBuildQuery(bool $isTimeSeries, bool $isTopNQuery, bool $isScanQuery, bool $isSelectQuery)
    {
        $context = ['foo' => 'bar'];

        $this->builder->interval('12-02-2015', '13-02-2015');

        $this->builder->shouldReceive('isScanQuery')
            ->once()
            ->andReturn($isScanQuery);

        if ($isScanQuery) {
            $scanQuery = $this->getScanQueryMock();
            $this->builder->shouldReceive('buildScanQuery')
                ->once()
                ->with($context)
                ->andReturn($scanQuery);

            /** @noinspection PhpUndefinedMethodInspection */
            $response = $this->builder->shouldAllowMockingProtectedMethods()->buildQuery($context);

            $this->assertEquals($scanQuery, $response);

            return;
        }

        $this->builder->shouldReceive('isTimeSeriesQuery')
            ->once()
            ->andReturn($isTimeSeries);

        if ($isTimeSeries) {
            $timeseries = $this->getTimeseriesQueryMock();
            $this->builder->shouldReceive('buildTimeSeriesQuery')
                ->once()
                ->with($context)
                ->andReturn($timeseries);

            /** @noinspection PhpUndefinedMethodInspection */
            $response = $this->builder->shouldAllowMockingProtectedMethods()->buildQuery($context);

            $this->assertEquals($timeseries, $response);

            return;
        }

        $this->builder->shouldReceive('isTopNQuery')
            ->once()
            ->andReturn($isTopNQuery);

        if ($isTopNQuery) {
            $topN = $this->getTopNQueryMock();
            $this->builder->shouldReceive('buildTopNQuery')
                ->once()
                ->with($context)
                ->andReturn($topN);

            /** @noinspection PhpUndefinedMethodInspection */
            $response = $this->builder->shouldAllowMockingProtectedMethods()->buildQuery($context);

            $this->assertEquals($topN, $response);

            return;
        }

        $this->builder->shouldReceive('isSelectQuery')
            ->once()
            ->andReturn($isSelectQuery);

        if ($isSelectQuery) {
            $selectQuery = $this->getSelectQueryMock();
            $this->builder->shouldReceive('buildSelectQuery')
                ->once()
                ->with($context)
                ->andReturn($selectQuery);

            /** @noinspection PhpUndefinedMethodInspection */
            $response = $this->builder->shouldAllowMockingProtectedMethods()->buildQuery($context);

            $this->assertEquals($selectQuery, $response);

            return;
        }

        $groupBy = $this->getGroupByQueryMock();

        $this->builder->shouldReceive('buildGroupByQuery')
            ->once()
            ->with($context, 'v2')
            ->andReturn($groupBy);

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->builder->shouldAllowMockingProtectedMethods()->buildQuery($context);

        $this->assertEquals($groupBy, $response);
    }

    public function testBuildTimeSeriesQueryWithoutIntervals()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify at least one interval');

        /** @noinspection PhpUndefinedMethodInspection */
        $this->builder->shouldAllowMockingProtectedMethods()->buildTimeSeriesQuery([]);
    }

    /**
     * @testWith ["theTime", {}, true, true, true, false, 0, ""]
     *           ["myTime", {"skipEmptyBuckets":true}, false, true, false, true, 5, ""]
     *           ["myTime", {"skipEmptyBuckets":true}, false, false, false, true, 5, ""]
     *           ["myTime", {"skipEmptyBuckets":true}, false, false, false, false, 5, "myTime", "desc", true]
     *           ["myTime", {"skipEmptyBuckets":true}, false, false, false, false, 5, "__time", "desc", false]
     *           ["myTime", {"skipEmptyBuckets":true}, false, false, false, false, 5, "Whatever", "desc"]
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param string $timeAlias
     * @param array  $context
     * @param bool   $withFilter
     * @param bool   $withVirtual
     * @param bool   $withAggregations
     * @param bool   $withPostAggregations
     * @param int    $limit
     * @param string $orderBy
     * @param string $direction
     * @param bool   $contextAsObject
     *
     * @throws \Exception
     */
    public function testBuildTimeSeriesQuery(
        string $timeAlias,
        array $context,
        bool $withFilter,
        bool $withVirtual,
        bool $withAggregations,
        bool $withPostAggregations,
        int $limit,
        string $orderBy,
        string $direction = "asc",
        bool $contextAsObject = true
    ) {
        $dataSource = 'phones';

        $this->builder->interval('12-02-2019/13-02-2019');
        $response = $this->builder->dataSource($dataSource);
        $this->assertEquals($this->builder, $response);

        $response = $this->builder->granularity('day');
        $this->assertEquals($this->builder, $response);

        $this->builder->select('__time', $timeAlias);
        if ($withVirtual) {
            $response = $this->builder->virtualColumn('concat(foo, bar)', 'fooBar');
            $this->assertEquals($this->builder, $response);
        }

        if ($withFilter) {
            $this->builder->where('field', '=', 'value');
        }

        if ($limit) {
            $this->builder->limit($limit);
        }

        if ($orderBy) {
            $this->builder->orderBy($orderBy, $direction);
        }

        if ($withAggregations) {
            $this->builder->sum('items', 'total');
        }

        if ($withPostAggregations) {
            $this->builder->divide('avg', ['field', 'total']);
        }

        $query = Mockery::mock('overload:' . TimeSeriesQuery::class);

        $query->shouldReceive('__construct')
            ->with($dataSource, new IsInstanceOf(IntervalCollection::class), 'day');

        if ($timeAlias != '__time') {
            $query->shouldReceive('setTimeOutputName')
                ->once()
                ->with($timeAlias);
        }

        if ($context || $contextAsObject) {
            $query->shouldReceive('setContext')
                ->once()
                ->with(new IsInstanceOf(TimeSeriesQueryContext::class));
        }

        if ($withFilter) {
            $query->shouldReceive('setFilter')
                ->once()
                ->with(new IsInstanceOf(FilterInterface::class));
        }

        if ($withAggregations) {
            $query->shouldReceive('setAggregations')
                ->once()
                ->with(new IsInstanceOf(AggregationCollection::class));
        }

        if ($withPostAggregations) {
            $query->shouldReceive('setPostAggregations')
                ->once()
                ->with(new IsInstanceOf(PostAggregationCollection::class));
        }

        if ($withVirtual) {
            $query->shouldReceive('setVirtualColumns')
                ->once()
                ->with(new IsInstanceOf(VirtualColumnCollection::class));
        }

        if ($limit && $limit != QueryBuilder::$DEFAULT_MAX_LIMIT) {
            $query->shouldReceive('setLimit')
                ->once()
                ->with($limit);
        }

        if ($direction == 'desc') {
            if ($orderBy == '__time' || $orderBy == $timeAlias) {
                $query->shouldReceive('setDescending')
                    ->once()
                    ->with(true);
            }
        }

        if ($contextAsObject) {
            $context = new TimeSeriesQueryContext($context);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->builder->shouldAllowMockingProtectedMethods()->buildTimeSeriesQuery($context);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception
     */
    public function testBuildTopNQueryWithoutLimit()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You should specify a limit to make use of a top query');

        $this->builder->interval('10-02-2019/11-02-2019');
        $this->builder->topN([]);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testBuildTopNQueryWithoutInterval()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify at least one interval');

        $this->builder->topN([]);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testBuildSelectQueryWithoutInterval()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify at least one interval');

        $this->builder->selectQuery([]);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception
     */
    public function testBuildSelectQueryWithoutLimit()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to supply a limit');

        $this->builder->interval('12-02-2019/13-02-2019');

        $this->builder->selectQuery([]);
    }

    /**
     * @testWith [{}, true, 50, true, true, "asc", true]
     *           [{}, false, 10, false, false, "asc", false]
     *           [{"priority": 10}, false, 10, false, true, "desc", true]
     *           [{"priority": 10}, true, 10, false, false, "desc", false]
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param array  $context
     * @param bool   $contextAsObject
     * @param int    $limit
     * @param bool   $withDimensions
     * @param bool   $withOrderBy
     * @param string $orderByDirection
     *
     * @param bool   $withPagingIdentifier
     *
     * @throws \Exception
     */
    public function testBuildSelectQuery(
        array $context,
        bool $contextAsObject,
        int $limit,
        bool $withDimensions,
        bool $withOrderBy,
        string $orderByDirection,
        bool $withPagingIdentifier
    ) {
        $this->builder->dataSource('wikipedia');
        $this->builder->interval('12-02-2019/13-02-2019');
        $this->builder->limit($limit);

        if ($withDimensions) {
            $this->builder->select('channel');
            $this->builder->select('delta');
        }

        $pagingIdentifier = [
            'wikipedia_2015-09-12T00:00:00.000Z_2015-09-13T00:00:00.000Z_2019-09-12T14:15:44.694Z' => 9,
        ];
        if ($withPagingIdentifier) {
            $this->builder->pagingIdentifier($pagingIdentifier);
        }

        $query = Mockery::mock('overload:' . SelectQuery::class);

        $descending = false;

        if ($withOrderBy) {
            $this->builder->orderBy('__time', $orderByDirection);

            if (OrderByDirection::validate($orderByDirection) == OrderByDirection::DESC) {
                $descending = true;
            }
        }

        $query->shouldReceive('__construct')
            ->once()
            ->with(
                'wikipedia',
                new IsInstanceOf(IntervalCollection::class),
                $limit,
                ($withDimensions ? new IsInstanceOf(DimensionCollection::class) : null),
                [], // @todo
                $descending
            );

        if ($context || $contextAsObject) {
            $query->shouldReceive('setContext')
                ->once()
                ->with(new IsInstanceOf(QueryContext::class));
        }

        if ($withPagingIdentifier) {
            $query->shouldReceive('setPagingIdentifier')
                ->once()
                ->with($pagingIdentifier);
        }

        if ($contextAsObject) {
            $context = new QueryContext($context);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->builder->shouldAllowMockingProtectedMethods()->buildSelectQuery($context);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testBuildScanQueryWithoutInterval()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify at least one interval');

        $this->builder->scan([]);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception
     */
    public function testBuildScanQueryWithIncorrectResultFormatType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid scanQuery resultFormat given');

        $this->builder->interval('12-02-2019/13-02-2019');
        $this->builder->scan([], 10, true, 'none');
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception
     */
    public function testBuildScanQueryWithoutCorrectDimensions()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only simple dimension or metric selects are available in a scan query.');

        $this->builder->interval('12-02-2019/13-02-2019');
        $this->builder->lookup('country', 'iso');
        $this->builder->scan([]);
    }

    /**
     * @testWith [true, true, {}, true, 10, null, false, "list", "", true]
     *           [false, false, {}, false, 50, 10, true, "compactedList", "channel", false]
     *           [true, false, {"maxRowsQueuedForOrdering":5}, true, 0, 200, false, "list", "__time", true]
     *           [false, true, {"maxRowsQueuedForOrdering":5}, false, 999999, 0, true, "compactedList", "__time", false]
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param bool     $withDimensions
     * @param bool     $withFilter
     * @param array    $context
     * @param bool     $contextAsObj
     * @param int      $limit
     * @param int|null $rowBatchSize
     * @param bool     $legacy
     * @param string   $resultFormat
     * @param string   $orderByField
     * @param bool     $asc
     *
     * @throws \Exception
     */
    public function testBuildScanQuery(
        bool $withDimensions,
        bool $withFilter,
        array $context,
        bool $contextAsObj,
        int $limit,
        ?int $rowBatchSize,
        bool $legacy,
        string $resultFormat,
        string $orderByField,
        bool $asc
    ) {
        $this->builder->interval('12-02-2019/13-02-2019');
        $this->builder->dataSource('wikipedia');

        if ($withDimensions) {
            $this->builder->select('channel');
            $this->builder->select('delta');
        }

        $filter = new SelectorFilter('cityName', 'Auburn');
        if ($withFilter) {
            $this->builder->where($filter);
        }

        if ($limit > 0) {
            $this->builder->limit($limit);
        }

        if (!empty($orderByField)) {
            $this->builder->orderBy($orderByField, $asc ? 'asc' : 'desc');
        }

        $query = Mockery::mock('overload:' . ScanQuery::class);

        $query->shouldReceive('__construct')
            ->once()
            ->with('wikipedia', new IsInstanceOf(IntervalCollection::class));

        if ($withDimensions) {
            $query->shouldReceive('setColumns')
                ->once()
                ->with(['channel', 'delta']);
        }

        if ($withFilter) {
            $query->shouldReceive('setFilter')
                ->once()
                ->with($filter);
        }

        if ($context || $contextAsObj) {
            $query->shouldReceive('setContext')
                ->once()
                ->with(new IsInstanceOf(ScanQueryContext::class));
        }

        if ($limit > 0 && $limit != QueryBuilder::$DEFAULT_MAX_LIMIT) {
            $query->shouldReceive('setLimit')
                ->once()
                ->with($limit);
        }

        $query->shouldReceive('setResultFormat')
            ->once()
            ->with($resultFormat);

        if ($rowBatchSize) {
            $query->shouldReceive('setBatchSize')
                ->once()
                ->with($rowBatchSize);
        }

        if ($orderByField == '__time') {
            $query->shouldReceive('setOrder')
                ->once()
                ->with($asc ? OrderByDirection::ASC : OrderByDirection::DESC);
        }

        $query->shouldReceive('setLegacy')
            ->once()
            ->with($legacy);

        if ($contextAsObj) {
            $context = new ScanQueryContext($context);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->builder->shouldAllowMockingProtectedMethods()->buildScanQuery($context, $rowBatchSize, $legacy,
            $resultFormat);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception
     */
    public function testBuildTopNQueryWithoutOrderBy()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You should specify a an order by direction to make use of a top query');
        $this->builder->interval('10-02-2019/11-02-2019');
        $this->builder->limit(10);

        $this->builder->topN([]);
    }

    /**
     * @testWith [true, true, true, true, true, "asc", {"minTopNThreshold":2}, true]
     *           [true, true, true, true, true, "asc", {"minTopNThreshold":2}, false]
     *           [false, false, false, false, false, "desc", {}, true]
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param bool   $withAggregations
     * @param bool   $withPostAggregations
     * @param bool   $withGranularity
     * @param bool   $withVirtual
     * @param bool   $withFilter
     * @param string $direction
     * @param array  $context
     * @param bool   $contextAsArray
     *
     * @throws \Exception
     */
    public function testBuildTopNQuery(
        bool $withAggregations,
        bool $withPostAggregations,
        bool $withGranularity,
        bool $withVirtual,
        bool $withFilter,
        string $direction,
        array $context,
        bool $contextAsArray
    ) {
        $dataSource = 'phones';

        $this->builder->interval('12-02-2019/13-02-2019');
        $this->builder->dataSource($dataSource);
        $this->builder->select('country_iso');
        $this->builder->limit(15);
        $this->builder->orderBy('suppliers', $direction);

        if ($withAggregations) {
            $this->builder->longSum('suppliers');
        }

        if ($withPostAggregations) {
            $this->builder->divide('avg', ['field', 'suppliers']);
        }

        if ($withGranularity) {
            $this->builder->granularity('day');
        }

        if ($withVirtual) {
            $this->builder->virtualColumn('concat(foo, bar)', 'fooBar');
        }

        if ($withFilter) {
            $this->builder->where('field', '=', 'value');
        }

        $query = Mockery::mock('overload:' . TopNQuery::class);

        $query->shouldReceive('__construct')
            ->once()
            ->with(
                $dataSource,
                new IsInstanceOf(IntervalCollection::class),
                new IsInstanceOf(DimensionInterface::class),
                15,
                'suppliers',
                $withGranularity ? 'day' : 'all'
            );

        if ($context) {
            $query->shouldReceive('setContext')
                ->once()
                ->with(new IsInstanceOf(TopNQueryContext::class));
        }

        if ($withFilter) {
            $query->shouldReceive('setFilter')
                ->once()
                ->with(new IsInstanceOf(FilterInterface::class));
        }

        if ($withAggregations) {
            $query->shouldReceive('setAggregations')
                ->once()
                ->with(new IsInstanceOf(AggregationCollection::class));
        }

        if ($withPostAggregations) {
            $query->shouldReceive('setPostAggregations')
                ->once()
                ->with(new IsInstanceOf(PostAggregationCollection::class));
        }

        if ($withVirtual) {
            $query->shouldReceive('setVirtualColumns')
                ->once()
                ->with(new IsInstanceOf(VirtualColumnCollection::class));
        }

        $query->shouldReceive('setDescending')
            ->once()
            ->with($direction == "desc");

        if (!$contextAsArray) {
            $context = new TopNQueryContext($context);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->builder->shouldAllowMockingProtectedMethods()->buildTopNQuery($context);
    }

    public function testBuildGroupByQueryWithoutInterval()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify at least one interval');

        /** @noinspection PhpUndefinedMethodInspection */
        $this->builder->shouldAllowMockingProtectedMethods()->buildGroupByQuery([]);
    }

    /**
     * @testWith ["v1", true, true, true, true, true, true]
     *           ["v1", false, false, true, false, true, false]
     *           ["v2", true, false, false, true, false, true]
     *           ["v2", false, false, false, false, false, false]
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param string $version
     * @param bool   $withArrayContext
     * @param bool   $withVirtual
     * @param bool   $withFilter
     * @param bool   $withLimit
     * @param bool   $withHaving
     * @param bool   $withPostAggregations
     *
     * @throws \Exception
     */
    public function testBuildGroupByQuery(
        string $version,
        bool $withArrayContext,
        bool $withVirtual,
        bool $withFilter,
        bool $withLimit,
        bool $withHaving,
        bool $withPostAggregations
    ) {
        $dataSource = 'drinks';

        $this->builder->select('cans');
        $this->builder->interval('12-02-2019/13-02-2019');
        $this->builder->dataSource($dataSource);
        $this->builder->longSum('liters');
        $this->builder->granularity('week');

        if ($withArrayContext) {
            $context    = ['timeout' => 60];
            $expectType = $version == 'v1' ? GroupByV1QueryContext::class : GroupByV2QueryContext::class;
        } else {
            $context = new GroupByV2QueryContext();
            $context->setFinalize(true);
            $expectType = GroupByV2QueryContext::class;
        }

        if ($withPostAggregations) {
            $this->builder->divide('avg', ['field', 'suppliers']);
        }

        if ($withVirtual) {
            $this->builder->virtualColumn('concat(foo, bar)', 'fooBar');
        }

        if ($withFilter) {
            $this->builder->where('field', '=', 'value');
        }

        if ($withLimit) {
            $this->builder->limit(89);
        }

        if ($withHaving) {
            $this->builder->having('field', '=', 'value');
        }

        $query = Mockery::mock('overload:' . GroupByQuery::class);

        $query->shouldReceive('__construct')
            ->once()
            ->with(
                $dataSource,
                new IsInstanceOf(DimensionCollection::class),
                new IsInstanceOf(IntervalCollection::class),
                new IsInstanceOf(AggregationCollection::class),
                'week'
            );

        $query->shouldReceive('setContext')
            ->once()
            ->with(new IsInstanceOf($expectType));

        if ($withFilter) {
            $query->shouldReceive('setFilter')
                ->once()
                ->with(new IsInstanceOf(FilterInterface::class));
        }

        if ($withVirtual) {
            $query->shouldReceive('setVirtualColumns')
                ->once()
                ->with(new IsInstanceOf(VirtualColumnCollection::class));
        }

        if ($withLimit) {
            $query->shouldReceive('setLimit')
                ->once()
                ->with(new IsInstanceOf(LimitInterface::class));
        }

        if ($withHaving) {
            $query->shouldReceive('setHaving')
                ->once()
                ->with(new IsInstanceOf(HavingFilterInterface::class));
        }

        if ($withPostAggregations) {
            $query->shouldReceive('setPostAggregations')
                ->once()
                ->with(new IsInstanceOf(PostAggregationCollection::class));
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->builder->shouldAllowMockingProtectedMethods()->buildGroupByQuery($context, $version);
    }

    /**
     * @return \Level23\Druid\Queries\TimeSeriesQuery|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected function getTimeseriesQueryMock()
    {
        return Mockery::mock(TimeSeriesQuery::class, ['test', new IntervalCollection()]);
    }

    /**
     * @return \Level23\Druid\Queries\ScanQuery|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     * @throws \Exception
     */
    protected function getScanQueryMock()
    {
        return Mockery::mock(ScanQuery::class,
            ['test', new IntervalCollection(new Interval('12-02-2015', '13-02-2015'))]);
    }

    /**
     * @return \Level23\Druid\Queries\GroupByQuery|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     * @throws \Exception
     */
    protected function getGroupByQueryMock()
    {
        return Mockery::mock(
            GroupByQuery::class,
            ['test', new DimensionCollection(), new IntervalCollection(new Interval('12-02-2015', '13-02-2015'))]
        );
    }

    /**
     * @return \Level23\Druid\Queries\TopNQuery|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     * @throws \Exception
     */
    protected function getTopNQueryMock()
    {
        return Mockery::mock(
            TopNQuery::class,
            [
                'test',
                new IntervalCollection(new Interval('12-02-2015', '13-02-2015')),
                new Dimension('age'),
                5,
                'messages',
            ]
        );
    }

    /**
     * @return \Level23\Druid\Queries\SelectQuery|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     * @throws \Exception
     */
    protected function getSelectQueryMock()
    {
        return Mockery::mock(
            SelectQuery::class,
            ['test', new IntervalCollection(new Interval('12-02-2015', '13-02-2015')), 50]
        );
    }
}