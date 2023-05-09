<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Queries;

use Mockery;
use Hamcrest\Core\IsEqual;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Hamcrest\Core\IsInstanceOf;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Queries\TopNQuery;
use Level23\Druid\Queries\ScanQuery;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Types\Granularity;
use GuzzleHttp\Client as GuzzleClient;
use Level23\Druid\Types\SortingOrder;
use Level23\Druid\Queries\SelectQuery;
use Level23\Druid\Queries\SearchQuery;
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
use Level23\Druid\DataSources\TableDataSource;
use Level23\Druid\VirtualColumns\VirtualColumn;
use Level23\Druid\Queries\SegmentMetadataQuery;
use Level23\Druid\DataSources\LookupDataSource;
use Level23\Druid\Dimensions\DimensionInterface;
use Level23\Druid\Context\GroupByV2QueryContext;
use Level23\Druid\Context\GroupByV1QueryContext;
use Level23\Druid\Responses\SelectQueryResponse;
use Level23\Druid\Extractions\ExtractionBuilder;
use Level23\Druid\Responses\SearchQueryResponse;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Context\TimeSeriesQueryContext;
use Level23\Druid\Responses\GroupByQueryResponse;
use Level23\Druid\Collections\DimensionCollection;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Responses\TimeSeriesQueryResponse;
use Level23\Druid\SearchFilters\ContainsSearchFilter;
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
        $guzzle = new GuzzleClient(['base_uri' => 'https://httpbin.org']);

        $this->client  = Mockery::mock(DruidClient::class, [[], $guzzle]);
        $this->builder = Mockery::mock(QueryBuilder::class, [$this->client, 'dataSourceName']);
        $this->builder->makePartial();
        $this->builder->shouldAllowMockingProtectedMethods();
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testExecute(): void
    {
        $context = ['foo' => 'bar'];
        $query   = $this->getTimeseriesQueryMock();

        $result = ['result' => 'here'];

        $responseObj = new TimeSeriesQueryResponse([], 'timestamp');

        $this->builder
            ->shouldReceive('getQuery')
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

        $response = $this->builder->interval('yesterday/now')->execute($context);

        $this->assertEquals($responseObj, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSelectVirtual(): void
    {
        Mockery::mock('overload:' . VirtualColumn::class)
            ->shouldReceive('__construct')
            ->with('foo + bar', 'fooBar', 'float');

        $response = $this->builder->selectVirtual('foo + bar', 'fooBar', 'float');

        $this->assertNotEquals([
            'type'       => 'default',
            'dimension'  => 'fooBar',
            'outputType' => 'string',
            'outputName' => 'fooBar',
        ], $this->builder->getDimensions()[0]->toArray());

        $this->assertEquals([
            'type'       => 'default',
            'dimension'  => 'fooBar',
            'outputType' => 'float',
            'outputName' => 'fooBar',
        ], $this->builder->getDimensions()[0]->toArray());

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @throws \ReflectionException
     */
    public function testGranularity(): void
    {
        $response = $this->builder->granularity('year');
        $this->assertEquals($this->builder, $response);

        $this->assertEquals('year', $this->getProperty($this->builder, 'granularity'));
    }

    public function testToJsonWithFailure(): void
    {
        $context = ['foo' => 'bar'];
        $query   = $this->getTimeseriesQueryMock();

        $this->builder
            ->shouldReceive('getQuery')
            ->with($context)
            ->once()
            ->andReturn($query);

        $query->shouldReceive('toArray')
            ->once()
            ->andThrows(InvalidArgumentException::class, 'json_encode error: blaat woei');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('json_encode error:');

        $this->builder->toJson($context);
    }

    public function testToJson(): void
    {
        $context = ['foo' => 'bar'];
        $query   = $this->getTimeseriesQueryMock();

        $result = ['result' => 'here'];

        $this->builder
            ->shouldReceive('getQuery')
            ->with($context)
            ->once()
            ->andReturn($query);

        $query->shouldReceive('toArray')
            ->once()
            ->andReturn($result);

        $response = $this->builder->toJson($context);

        $this->assertEquals(json_encode($result, JSON_PRETTY_PRINT), $response);
    }

    public function testToArray(): void
    {
        $context = ['foo' => 'bar'];
        $query   = $this->getTimeseriesQueryMock();

        $result = ['result' => 'here'];

        $this->builder
            ->shouldReceive('getQuery')
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
     * Test the subtotals
     *
     * @throws \ReflectionException
     */
    public function testSubtotals(): void
    {
        $subtotals = [['country', 'city'], ['country'], []];
        $this->builder->subtotals($subtotals);

        $this->assertEquals($subtotals, $this->getProperty($this->builder, 'subtotals'));
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException|\GuzzleHttp\Exception\GuzzleException
     */
    public function testTimeseries(): void
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
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function testTopN(): void
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
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function testScan(?int $rowBatchSize, bool $legacy, string $resultFormat): void
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
     * @throws \ReflectionException
     */
    public function testMetrics(): void
    {
        $metrics = ['added', 'deleted'];

        $this->assertEquals([], $this->getProperty($this->builder, 'metrics'));

        $this->builder->metrics($metrics);

        $this->assertEquals($metrics, $this->getProperty($this->builder, 'metrics'));
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function testSelect(): void
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testSegmentMetadata(): void
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
    public function testDatasource(): void
    {
        $response = $this->builder->dataSource('myFavorite');
        $this->assertEquals($this->builder, $response);

        $this->assertEquals(
            new TableDataSource('myFavorite'),
            $this->getProperty($this->builder, 'dataSource')
        );

        $lookupDataSource = new LookupDataSource('myFunc');
        $this->builder->from($lookupDataSource);
        $this->assertEquals(
            $lookupDataSource,
            $this->getProperty($this->builder, 'dataSource')
        );
    }

    /**
     * @testWith [{"__time":"hour"}, 5, "hour", true]
     *           [{"__time":"hour", "name":"name"}, 5, "hour", false]
     *           [{"__time":"hour"}, null, "hour", false]
     *           [{"__time":"hour"}, 5, null, false]
     *
     * @param array<string,string> $dimensions
     * @param int|null             $limit
     * @param string|null          $orderBy
     * @param bool                 $expected
     */
    public function testIsTopNQuery(array $dimensions, ?int $limit, ?string $orderBy, bool $expected): void
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

    /**
     * @return array<int,array<int,array<int|string,string>|bool|Dimension>>
     */
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
     * @param array<int|string,string>|Dimension $dimension
     * @param bool                               $expected
     */
    public function testIsTimeSeriesQuery($dimension, bool $expected): void
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
    public function testIsSelectQuery(bool $withIdentifier, bool $withAggregations): void
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
     * @testWith [true]
     *           [false]
     *
     * @param bool $withSearchFilter
     */
    public function testIsSearchQuery(bool $withSearchFilter): void
    {
        if ($withSearchFilter) {
            $this->builder->searchContains('wikipedia');
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals($withSearchFilter, $this->builder->shouldAllowMockingProtectedMethods()->isSearchQuery());
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
    public function testIsScanQuery(bool $withCorrectDimensions, bool $withAggregations): void
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
    public function testIsDimensionsListScanCompliant(bool $onlyDimensions, bool $alias, bool $extractions): void
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
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function testGroupByV1(): void
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGroupBy(): void
    {
        $context = ['foo' => 'bar'];
        $query   = $this->getGroupByQueryMock();

        $result       = [['event' => ['result' => 'here']]];
        $parsedResult = new GroupByQueryResponse($result);

        $this->builder->interval('now - 1 week/now');
        $this->builder->shouldReceive('buildGroupByQuery')
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
            ->andReturn($parsedResult);

        $response = $this->builder->groupBy($context);

        $this->assertEquals($parsedResult, $response);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function testSearch(): void
    {
        $context = ['foo' => 'bar'];
        $query   = $this->getSearchQueryMock();

        $result       = [['event' => ['result' => 'here']]];
        $parsedResult = new SearchQueryResponse($result);

        $this->builder->shouldReceive('buildSearchQuery')
            ->with($context, SortingOrder::STRLEN)
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

        $response = $this->builder->search($context, SortingOrder::STRLEN);

        $this->assertEquals($parsedResult, $response);
    }

    /**
     * @throws \ReflectionException
     */
    public function testPagingIdentifier(): void
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
     * @param bool $isSearchQuery
     *
     * @throws \Exception
     * @testWith [true, false, false, false, false]
     *           [false, true, false, false, false]
     *           [false, false, true, false, false]
     *           [false, false, false, true, false]
     *           [false, false, false, false, true]
     *           [false, false, false, false, false]
     *
     */
    public function testGetQuery(
        bool $isTimeSeries,
        bool $isTopNQuery,
        bool $isScanQuery,
        bool $isSelectQuery,
        bool $isSearchQuery
    ): void {
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

            $response = $this->builder->getQuery($context);

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

            $response = $this->builder->getQuery($context);

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

            $response = $this->builder->getQuery($context);

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

            $response = $this->builder->getQuery($context);

            $this->assertEquals($selectQuery, $response);

            return;
        }

        $this->builder->shouldReceive('isSearchQuery')
            ->once()
            ->andReturn($isSearchQuery);

        if ($isSearchQuery) {
            $searchQuery = $this->getSearchQueryMock();
            $this->builder->shouldReceive('buildSearchQuery')
                ->once()
                ->with($context)
                ->andReturn($searchQuery);

            $response = $this->builder->getQuery($context);

            $this->assertEquals($searchQuery, $response);

            return;
        }

        $groupBy = $this->getGroupByQueryMock();

        $this->builder->shouldReceive('buildGroupByQuery')
            ->once()
            ->with($context)
            ->andReturn($groupBy);

        $response = $this->builder->getQuery($context);

        $this->assertEquals($groupBy, $response);
    }

    public function testBuildTimeSeriesQueryWithoutIntervals(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify at least one interval');

        /** @noinspection PhpUndefinedMethodInspection */
        $this->builder->shouldAllowMockingProtectedMethods()->buildTimeSeriesQuery([]);
    }

    /**
     * @testWith ["theTime", {}, true, true, true, false, 0]
     *           ["myTime", {"skipEmptyBuckets":true}, false, true, false, true, 5]
     *           ["myTime", {"skipEmptyBuckets":true}, false, false, false, true, 5]
     *           ["myTime", {"skipEmptyBuckets":true}, false, false, false, false, 5, "asc", true]
     *           ["myTime", {"skipEmptyBuckets":true}, false, false, false, false, 5, "desc", false]
     *           ["myTime", {"skipEmptyBuckets":true}, false, false, false, false, 5, "desc", false, true]
     *           ["__time", {"skipEmptyBuckets":true}, false, false, false, false, 5, "asc", false, true]
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param string             $timeAlias
     * @param array<string,bool> $context
     * @param bool               $withFilter
     * @param bool               $withVirtual
     * @param bool               $withAggregations
     * @param bool               $withPostAggregations
     * @param int                $limit
     * @param string             $direction
     * @param bool               $contextAsObject
     * @param bool               $useLegacyOrderBy
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
        string $direction = 'asc',
        bool $contextAsObject = true,
        bool $useLegacyOrderBy = false
    ): void {
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

        if ($useLegacyOrderBy) {
            $this->builder->orderBy("__time", $direction);
        } else {
            $this->builder->orderByDirection($direction);
        }

        if ($withAggregations) {
            $this->builder->sum('items', 'total');
        }

        if ($withPostAggregations) {
            $this->builder->divide('avg', ['field', 'total']);
        }

        $query = Mockery::mock('overload:' . TimeSeriesQuery::class);

        $query->shouldReceive('__construct')
            ->withArgs(function ($givenDataSource, $intervals, $granularity) use ($dataSource) {

                $this->assertEquals(new TableDataSource($dataSource), $givenDataSource);
                $this->assertInstanceOf(IntervalCollection::class, $intervals);
                $this->assertEquals('day', $granularity);

                return true;
            });

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

        if ($useLegacyOrderBy) {
            $this->builder->shouldReceive('legacyIsOrderByDirectionDescending')
                ->once()
                ->with($timeAlias)
                ->andReturn(($direction == 'desc'));
        }

        $query->shouldReceive('setDescending')
            ->once()
            ->with($direction == 'desc');

        if ($contextAsObject) {
            $context = new TimeSeriesQueryContext($context);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->builder->shouldAllowMockingProtectedMethods()->buildTimeSeriesQuery($context);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function testBuildTopNQueryWithoutLimit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You should specify a limit to make use of a top query');

        $this->builder->interval('10-02-2019/11-02-2019');
        $this->builder->topN([]);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException|\GuzzleHttp\Exception\GuzzleException
     */
    public function testBuildTopNQueryWithoutInterval(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify at least one interval');

        $this->builder->topN([]);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testBuildSelectQueryWithoutInterval(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify at least one interval');

        $this->builder->selectQuery([]);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function testBuildSelectQueryWithoutLimit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to supply a limit');

        $this->builder->interval('12-02-2019/13-02-2019');

        $this->builder->selectQuery([]);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException|\GuzzleHttp\Exception\GuzzleException
     */
    public function testBuildSearchQueryWithoutInterval(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify at least one interval');

        $this->builder->search([]);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function testBuildSearchQueryWithoutSearchFilter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify a search filter!');

        $this->builder->interval('12-02-2019/13-02-2019');
        $this->builder->search([]);
    }

    /**
     * @testWith [{"priority": 10}, "strlen", "day", true, 0, {}, true]
     *           [{"priority": 10}, "alphanumeric", "hour", false, 10, {"0": "channel", "1": "namespace"}, true]
     *           [{}, "alphanumeric", "hour", false, 20, {}, false]
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param array<string,int>        $context
     * @param string                   $sortingOrder
     * @param string                   $granularity
     * @param bool                     $contextAsObject
     * @param int                      $limit
     * @param array<string|int,string> $dimensions
     * @param bool                     $withFilter
     *
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function testBuildSearchQuery(
        array $context,
        string $sortingOrder,
        string $granularity,
        bool $contextAsObject,
        int $limit,
        array $dimensions,
        bool $withFilter
    ): void {
        $this->builder->dataSource('wikipedia');
        $this->builder->granularity($granularity);
        $this->builder->interval('12-02-2019/13-02-2019');

        if ($limit) {
            $this->builder->limit($limit);
        }

        if (count($dimensions) > 0) {
            $this->builder->dimensions($dimensions);
        }
        if ($withFilter) {
            $this->builder->where('channel', '=', 'en.wikipedia');
        }

        $this->builder->searchContains('wiki', true);

        $filter = new ContainsSearchFilter('wiki', true);

        $this->assertEquals($filter, $this->getProperty($this->builder, 'searchFilter'));

        $query = Mockery::mock('overload:' . SearchQuery::class);

        $query->shouldReceive('__construct')
            ->once()
            ->withArgs(function ($givenDataSource, $givenGranularity, $intervals, $givenFilter) use (
                $filter,
                $granularity
            ) {
                $this->assertEquals(new TableDataSource('wikipedia'), $givenDataSource);
                $this->assertEquals($granularity, $givenGranularity);
                $this->assertInstanceOf(IntervalCollection::class, $intervals);
                $this->assertEquals($filter, $givenFilter);

                return true;
            });

        if ($context || $contextAsObject) {
            $query->shouldReceive('setContext')
                ->once()
                ->with(new IsInstanceOf(QueryContext::class));
        }

        if ($limit) {
            $query->shouldReceive('setLimit')
                ->once()
                ->with($limit);
        }

        if (count($dimensions) > 0) {
            $query->shouldReceive('setDimensions')
                ->once()
                ->with($dimensions);
        }

        if ($withFilter) {
            $query->shouldReceive('setFilter')
                ->once()
                ->with(new IsInstanceOf(FilterInterface::class));
        }

        $query->shouldReceive('setSort')
            ->once()
            ->with($sortingOrder);

        if ($contextAsObject) {
            $context = new QueryContext($context);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->builder->shouldAllowMockingProtectedMethods()->buildSearchQuery($context, $sortingOrder);
    }

    /**
     * @testWith [{}, true, 50, true, true, "asc", true, true]
     *           [{}, false, 10, false, false, "asc", false, false]
     *           [{"priority": 10}, false, 10, false, true, "desc", true, false]
     *           [{"priority": 10}, true, 10, false, true, "desc", false, true]
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param array<string,int> $context
     * @param bool              $contextAsObject
     * @param int               $limit
     * @param bool              $withDimensions
     * @param bool              $withOrderBy
     * @param string            $orderByDirection
     * @param bool              $withPagingIdentifier
     * @param bool              $useLegacyOrderBy
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
        bool $withPagingIdentifier,
        bool $useLegacyOrderBy = false
    ): void {
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

        if ($withOrderBy && $useLegacyOrderBy) {
            $this->builder->orderBy('__time', $orderByDirection);

            if (OrderByDirection::validate($orderByDirection) == OrderByDirection::DESC) {
                $descending = true;
            }
        }

        if ($withOrderBy && !$useLegacyOrderBy) {
            $this->builder->orderByDirection($orderByDirection);

            if (OrderByDirection::validate($orderByDirection) == OrderByDirection::DESC) {
                $descending = true;
            }
        }

        $query->shouldReceive('__construct')
            ->once()
            ->withArgs(function ($givenDataSource, $intervals, $givenLimit, $dimensions, $metrics, $givenDescending) use
            (
                $descending,
                $withDimensions,
                $limit
            ) {
                $this->assertEquals(new TableDataSource('wikipedia'), $givenDataSource);
                $this->assertInstanceOf(IntervalCollection::class, $intervals);
                $this->assertEquals($limit, $givenLimit);
                if ($withDimensions) {
                    $this->assertInstanceOf(DimensionCollection::class, $dimensions);
                } else {
                    $this->assertNull($dimensions);
                }
                $this->assertEquals([], $metrics);
                $this->assertEquals($descending, $givenDescending);

                return true;
            });

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
     * @throws \Level23\Druid\Exceptions\QueryResponseException|\GuzzleHttp\Exception\GuzzleException
     */
    public function testBuildScanQueryWithoutInterval(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify at least one interval');

        $this->builder->scan([]);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function testBuildScanQueryWithIncorrectResultFormatType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid scanQuery resultFormat given');

        $this->builder->interval('12-02-2019/13-02-2019');
        $this->builder->scan([], 10, true, 'none');
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function testBuildScanQueryWithoutCorrectDimensions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only simple dimension or metric selects are available in a scan query.');

        $this->builder->interval('12-02-2019/13-02-2019');
        $this->builder->lookup('country', 'iso');
        $this->builder->scan([]);
    }

    /**
     * @testWith [true, false, true, {}, true, 10, 20, null, false, "list", "", true, true]
     *           [false, true, false, {}, false, 50, null, 10, true, "compactedList", "channel", false, true]
     *           [true, false, false, {"maxRowsQueuedForOrdering":5}, true, 1, 1, 200, false, "list", "__time", true, true]
     *           [false, false, true, {}, false, 12, 12, 0, true, "compactedList", "__time", false, true]
     *           [true, false, false, {"maxRowsQueuedForOrdering":5}, false, 0, 0, 200, false, "list", "__time", true, false]
     *           [false, false, true, {}, false, 12, null, 0, true, "compactedList", "__time", false, false]
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param bool              $withDimensions
     * @param bool              $withVirtual
     * @param bool              $withFilter
     * @param array<string,int> $context
     * @param bool              $contextAsObj
     * @param int|null          $limit
     * @param int|null          $offset
     * @param int|null          $rowBatchSize
     * @param bool              $legacy
     * @param string            $resultFormat
     * @param string            $orderByField
     * @param bool              $asc
     * @param bool              $useLegacyOrderBy
     *
     * @throws \Exception
     */
    public function testBuildScanQuery(
        bool $withDimensions,
        bool $withVirtual,
        bool $withFilter,
        array $context,
        bool $contextAsObj,
        ?int $limit,
        ?int $offset,
        ?int $rowBatchSize,
        bool $legacy,
        string $resultFormat,
        string $orderByField,
        bool $asc,
        bool $useLegacyOrderBy = true
    ): void {
        $this->builder->interval('12-02-2019/13-02-2019');
        $this->builder->dataSource('wikipedia');

        if ($withDimensions) {
            $this->builder->select('channel');
            $this->builder->select('delta');
        }

        if ($withVirtual) {
            $this->builder->virtualColumn('concat(foo, bar)', 'fooBar');
        }

        $filter = new SelectorFilter('cityName', 'Auburn');
        if ($withFilter) {
            $this->builder->where($filter);
        }

        if ($limit > 0 || $offset > 0) {
            $this->builder->limit($limit, $offset);
        }

        if (!empty($orderByField) && $useLegacyOrderBy) {
            $this->builder->orderBy($orderByField, $asc ? 'asc' : 'desc');
        }

        if (!$useLegacyOrderBy) {
            $this->builder->orderByDirection($asc ? 'asc' : 'desc');
        }

        $query = Mockery::mock('overload:' . ScanQuery::class);

        $query->shouldReceive('__construct')
            ->once()
            ->with(
                new IsEqual(new TableDataSource('wikipedia')),
                new IsInstanceOf(IntervalCollection::class)
            );

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

        if ($limit > 0) {
            $query->shouldReceive('setLimit')
                ->once()
                ->with($limit);
        }

        if ($offset > 0) {
            $query->shouldReceive('setOffset')
                ->once()
                ->with($offset);
        }

        $query->shouldReceive('setResultFormat')
            ->once()
            ->with($resultFormat);

        if ($rowBatchSize !== null && $rowBatchSize > 0) {
            $query->shouldReceive('setBatchSize')
                ->once()
                ->with($rowBatchSize);
        }

        if ($withVirtual) {
            $query->shouldReceive('setVirtualColumns')
                ->once()
                ->with(new IsInstanceOf(VirtualColumnCollection::class));
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
        $this->builder->shouldAllowMockingProtectedMethods()->buildScanQuery(
            $context,
            $rowBatchSize,
            $legacy,
            $resultFormat
        );
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function testBuildTopNQueryWithoutOrderBy(): void
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
     * @param bool              $withAggregations
     * @param bool              $withPostAggregations
     * @param bool              $withGranularity
     * @param bool              $withVirtual
     * @param bool              $withFilter
     * @param string            $direction
     * @param array<string,int> $context
     * @param bool              $contextAsArray
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
    ): void {
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
                new IsEqual(new TableDataSource($dataSource)),
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

    public function testBuildGroupByQueryWithoutInterval(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to specify at least one interval');

        /** @noinspection PhpUndefinedMethodInspection */
        $this->builder->shouldAllowMockingProtectedMethods()->buildGroupByQuery([]);
    }

    /**
     * @testWith ["v1", true, true, true, true, true, true, true]
     *           ["v1", false, false, true, false, true, false, false]
     *           ["v2", true, false, false, true, false, true, false]
     *           ["v2", false, false, false, false, false, false, true]
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
     * @param bool   $withSubtotals
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
        bool $withPostAggregations,
        bool $withSubtotals
    ): void {
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

        $subtotals = [['country', 'city'], ['country'], []];
        if ($withSubtotals) {
            $this->builder->subtotals($subtotals);
        }

        $query = Mockery::mock('overload:' . GroupByQuery::class);

        $query->shouldReceive('__construct')
            ->once()
            ->with(
                new IsEqual(new TableDataSource($dataSource)),
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

        if ($withSubtotals) {
            $query->shouldReceive('setSubtotals')
                ->once()
                ->with($subtotals);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->builder->shouldAllowMockingProtectedMethods()->buildGroupByQuery($context, $version);
    }

    /**
     * @return \Level23\Druid\Queries\TimeSeriesQuery|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected function getTimeseriesQueryMock()
    {
        return Mockery::mock(
            TimeSeriesQuery::class,
            [new TableDataSource('test'), new IntervalCollection()]
        );
    }

    /**
     * @return \Level23\Druid\Queries\ScanQuery|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     * @throws \Exception
     */
    protected function getScanQueryMock()
    {
        return Mockery::mock(
            ScanQuery::class,
            [
                new TableDataSource('test'),
                new IntervalCollection(new Interval('12-02-2015', '13-02-2015')),
            ]
        );
    }

    /**
     * @return \Level23\Druid\Queries\GroupByQuery|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     * @throws \Exception
     */
    protected function getGroupByQueryMock()
    {
        return Mockery::mock(
            GroupByQuery::class,
            [
                new TableDataSource('test'),
                new DimensionCollection(),
                new IntervalCollection(new Interval('12-02-2015', '13-02-2015')),
            ]
        );
    }

    /**
     * @return \Level23\Druid\Queries\GroupByQuery|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     * @throws \Exception
     */
    protected function getSearchQueryMock()
    {
        return Mockery::mock(
            SearchQuery::class,
            [
                new TableDataSource('test'),
                Granularity::DAY,
                new IntervalCollection(new Interval('12-02-2015', '13-02-2015')),
                new ContainsSearchFilter('wikipedia', false),
            ]
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
                new TableDataSource('test'),
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
            [
                new TableDataSource('test'),
                new IntervalCollection(new Interval('12-02-2015', '13-02-2015')),
                50,
            ]
        );
    }
}
