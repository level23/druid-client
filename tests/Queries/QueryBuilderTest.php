<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Queries;

use Mockery;
use tests\TestCase;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Hamcrest\Core\IsInstanceOf;
use Level23\Druid\Queries\TopNQuery;
use Level23\Druid\Types\Granularity;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Queries\GroupByQuery;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\Queries\QueryInterface;
use Level23\Druid\Queries\TimeSeriesQuery;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Extractions\UpperExtraction;
use Level23\Druid\VirtualColumns\VirtualColumn;
use Level23\Druid\Queries\SegmentMetadataQuery;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Context\TimeSeriesQueryContext;
use Level23\Druid\Collections\DimensionCollection;
use Level23\Druid\Collections\VirtualColumnCollection;

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

        $normalized = ['normalized' => 'result'];

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
            ->andReturn($normalized);

        $response = $this->builder->execute($context);

        $this->assertEquals($normalized, $response);
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

        $this->builder->shouldReceive('select', 'fooBar');
        $this->builder->selectVirtual('concat(foo, bar)', 'fooBar');
    }

    public function testGranularity()
    {
        Mockery::mock(Granularity::class)
            ->shouldReceive('validate')
            ->with('year')
            ->andReturn('year');

        $this->builder->granularity('year');

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

        $result       = ['event' => ['result' => 'here']];
        $parsedResult = ['result' => 'here'];

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
            ->andReturn($parsedResult);

        $response = $this->builder->timeseries($context);

        $this->assertEquals($parsedResult, $response);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testTopN()
    {
        $context = ['foo' => 'bar'];
        $query   = $this->getTopNQueryMock();

        $result       = ['event' => ['result' => 'here']];
        $parsedResult = ['result' => 'here'];

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
            ->andReturn($parsedResult);

        $response = $this->builder->topN($context);

        $this->assertEquals($parsedResult, $response);
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

        $result       = ['event' => ['result' => 'here']];
        $parsedResult = ['result' => 'here'];

        $this->client->shouldReceive('executeQuery')
            ->with(new IsInstanceOf(SegmentMetadataQuery::class))
            ->once()
            ->andReturn($result);

        $query->shouldReceive('parseResponse')
            ->once()
            ->with($result)
            ->andReturn($parsedResult);

        $response = $this->builder->segmentMetadata();

        $this->assertEquals($parsedResult, $response);
    }

    public function testDatasource()
    {
        $this->builder->dataSource('myFavorite');

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
    public function testIsTopNQuery(array $dimensions, int $limit = null, string $orderBy = null, bool $expected)
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
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testGroupByV1()
    {
        $context = ['foo' => 'bar'];
        $query   = $this->getGroupByQueryMock();

        $result       = ['event' => ['result' => 'here']];
        $parsedResult = ['result' => 'here'];

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
     */
    public function testGroupBy()
    {
        $context = ['foo' => 'bar'];
        $query   = $this->getGroupByQueryMock();

        $result       = ['event' => ['result' => 'here']];
        $parsedResult = ['result' => 'here'];

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
     * @param bool $isTimeseries
     * @param bool $isTopNQuery
     *
     * @testWith [true, false]
     *           [false, true]
     *           [false, false]
     */
    public function testBuildQuery(bool $isTimeseries, bool $isTopNQuery)
    {
        $context = ['foo' => 'bar'];

        $this->builder->shouldReceive('isTimeSeriesQuery')
            ->once()
            ->andReturn($isTimeseries);

        if ($isTimeseries) {
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

        $groupBy = $this->getGroupByQueryMock();

        $this->builder->shouldReceive('buildGroupByQuery')
            ->once()
            ->with($context)
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
     * @testWith ["theTime", {}, true, true, 0, ""]
     *           ["myTime", {"skipEmptyBuckets":true}, false, false, 5, ""]
     *           ["myTime", {"skipEmptyBuckets":true}, false, false, 5, "myTime", "desc"]
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param string $timeAlias
     * @param array  $context
     * @param bool   $withFilter
     * @param bool   $withVirtual
     * @param int    $limit
     * @param string $orderBy
     * @param string $direction
     *
     * @throws \Exception
     * @todo                : finish me
     *
     */
    public function testBuildTimeSeriesQuery(
        string $timeAlias,
        array $context,
        bool $withFilter,
        bool $withVirtual,
        int $limit,
        string $orderBy,
        string $direction = "asc"
    ) {
        $dataSource = 'phones';

        $this->builder->interval('12-02-2019/13-02-2019');
        $this->builder->dataSource($dataSource);
        $this->builder->granularity('day');
        $this->builder->select('__time', $timeAlias);
        if ($withVirtual) {
            $this->builder->virtualColumn('concat(foo, bar)', 'fooBar');
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

        $query = Mockery::mock('overload:' . TimeSeriesQuery::class);

        $query->shouldReceive('__construct')
            ->with($dataSource, new IsInstanceOf(IntervalCollection::class), 'day');

        if ($timeAlias != '__time') {
            $query->shouldReceive('setTimeOutputName')
                ->once()
                ->with($timeAlias);
        }

        if ($context) {
            $query->shouldReceive('setContext')
                ->once()
                ->with(new IsInstanceOf(TimeSeriesQueryContext::class));
        }

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

        if ($limit && $limit != QueryBuilder::$DEFAULT_MAX_LIMIT) {
            $query->shouldReceive('setLimit')
                ->once()
                ->with($limit);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $this->builder->shouldAllowMockingProtectedMethods()->buildTimeSeriesQuery($context);
    }

    /**
     * @return \Level23\Druid\Queries\TimeSeriesQuery|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected function getTimeseriesQueryMock()
    {
        return Mockery::mock(TimeSeriesQuery::class, ['test', new IntervalCollection()]);
    }

    /**
     * @return \Level23\Druid\Queries\GroupByQuery|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected function getGroupByQueryMock()
    {
        return Mockery::mock(
            GroupByQuery::class,
            ['test', new DimensionCollection(), new IntervalCollection()]
        );
    }

    /**
     * @return \Level23\Druid\Queries\TopNQuery|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected function getTopNQueryMock()
    {
        return Mockery::mock(
            TopNQuery::class,
            ['test', new IntervalCollection(), new Dimension('age'), 5, 'messages']
        );
    }
}