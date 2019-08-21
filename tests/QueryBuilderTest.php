<?php
declare(strict_types=1);

namespace tests\Level23\Druid;

use Level23\Druid\Collections\DimensionCollection;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\DruidClient;
use Level23\Druid\Queries\GroupByQuery;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\Queries\TimeSeriesQuery;
use Level23\Druid\Queries\TopNQuery;
use Mockery;
use tests\TestCase;

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
        $this->builder = Mockery::mock(QueryBuilder::class, [$this->client, 'http://']);
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