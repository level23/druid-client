<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Concerns;

use Level23\Druid\Aggregations\CountAggregator;
use Level23\Druid\Aggregations\DistinctCountAggregator;
use Level23\Druid\Aggregations\FirstAggregator;
use Level23\Druid\Aggregations\LastAggregator;
use Level23\Druid\Aggregations\MaxAggregator;
use Level23\Druid\Aggregations\MinAggregator;
use Level23\Druid\Aggregations\SumAggregator;
use Level23\Druid\DruidClient;
use Level23\Druid\QueryBuilder;
use Mockery;
use tests\TestCase;

class HasAggregationsTest extends TestCase
{
    /**
     * @var \Level23\Druid\DruidClient
     */
    protected $client;

    /**
     * @var \Level23\Druid\QueryBuilder|\Mockery\MockInterface|\Mockery\LegacyMockInterface
     */
    protected $builder;

    public function testGetAggregations()
    {
        $this->builder->sum('messages');
        $this->builder->first('age');

        $aggregations = $this->builder->getAggregations();

        $this->assertEquals(2, count($aggregations));

        $this->assertInstanceOf(SumAggregator::class, $aggregations[0]);
        $this->assertInstanceOf(FirstAggregator::class, $aggregations[1]);
    }

    public function setUp(): void
    {
        $this->client  = new DruidClient([]);
        $this->builder = Mockery::mock(QueryBuilder::class, [$this->client, 'http://']);
        $this->builder->makePartial();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSum()
    {
        Mockery::mock('overload:' . SumAggregator::class)
            ->shouldReceive('__construct')
            ->with('messages', 'nrOfMessages', 'float')
            ->once();

        $response = $this->builder->sum('messages', 'nrOfMessages', 'float');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSumDefaults()
    {
        Mockery::mock('overload:' . SumAggregator::class)
            ->shouldReceive('__construct')
            ->with('messages', '', 'long')
            ->once();

        $response = $this->builder->sum('messages');
        $this->assertEquals($this->builder, $response);
    }

    public function testLongSum()
    {
        $this->builder->shouldReceive('sum')
            ->with('messages', '', 'long')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->longSum('messages');
        $this->assertEquals($this->builder, $response);
    }

    public function testDoubleSum()
    {
        $this->builder->shouldReceive('sum')
            ->with('messages', '', 'double')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->doubleSum('messages');
        $this->assertEquals($this->builder, $response);
    }

    public function testFloatSum()
    {
        $this->builder->shouldReceive('sum')
            ->with('messages', '', 'float')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->floatSum('messages');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCount()
    {
        Mockery::mock('overload:' . CountAggregator::class)
            ->shouldReceive('__construct')
            ->with('totals')
            ->once();

        $response = $this->builder->count('totals');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDistinctCount()
    {
        Mockery::mock('overload:' . DistinctCountAggregator::class)
            ->shouldReceive('__construct')
            ->with('message_id', 'messageIds', 32768)
            ->once();

        $response = $this->builder->distinctCount('message_id', 'messageIds', 32768);
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDistinctCountDefaults()
    {
        Mockery::mock('overload:' . DistinctCountAggregator::class)
            ->shouldReceive('__construct')
            ->with('message_id', 'message_id', 16384)
            ->once();

        $response = $this->builder->distinctCount('message_id');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMin()
    {
        Mockery::mock('overload:' . MinAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', 'minAge', 'float')
            ->once();

        $response = $this->builder->min('age', 'minAge', 'float');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMinDefaults()
    {
        Mockery::mock('overload:' . MinAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', '', 'long')
            ->once();

        $response = $this->builder->min('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testLongMin()
    {
        $this->builder->shouldReceive('min')
            ->with('age', '', 'long')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->longMin('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testDoubleMin()
    {
        $this->builder->shouldReceive('min')
            ->with('age', '', 'double')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->doubleMin('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testFloatMin()
    {
        $this->builder->shouldReceive('min')
            ->with('age', '', 'float')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->floatMin('age');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMax()
    {
        Mockery::mock('overload:' . MaxAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', 'maxAge', 'float')
            ->once();

        $response = $this->builder->max('age', 'maxAge', 'float');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMaxDefaults()
    {
        Mockery::mock('overload:' . MaxAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', '', 'long')
            ->once();

        $response = $this->builder->max('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testLongMax()
    {
        $this->builder->shouldReceive('max')
            ->with('age', '', 'long')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->longMax('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testDoubleMax()
    {
        $this->builder->shouldReceive('max')
            ->with('age', '', 'double')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->doubleMax('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testFloatMax()
    {
        $this->builder->shouldReceive('max')
            ->with('age', '', 'float')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->floatMax('age');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFirst()
    {
        Mockery::mock('overload:' . FirstAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', 'firstAge', 'float')
            ->once();

        $response = $this->builder->first('age', 'firstAge', 'float');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFirstDefaults()
    {
        Mockery::mock('overload:' . FirstAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', '', 'long')
            ->once();

        $response = $this->builder->first('age');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLast()
    {
        Mockery::mock('overload:' . LastAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', 'lastAge', 'float')
            ->once();

        $response = $this->builder->last('age', 'lastAge', 'float');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLastDefaults()
    {
        Mockery::mock('overload:' . LastAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', '', 'long')
            ->once();

        $response = $this->builder->last('age');
        $this->assertEquals($this->builder, $response);
    }
}