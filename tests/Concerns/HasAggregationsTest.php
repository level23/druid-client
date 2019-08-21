<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Concerns;

use Level23\Druid\Aggregations\AggregatorInterface;
use Level23\Druid\Aggregations\CountAggregator;
use Level23\Druid\Aggregations\DistinctCountAggregator;
use Level23\Druid\Aggregations\FilteredAggregator;
use Level23\Druid\Aggregations\FirstAggregator;
use Level23\Druid\Aggregations\JavascriptAggregator;
use Level23\Druid\Aggregations\LastAggregator;
use Level23\Druid\Aggregations\MaxAggregator;
use Level23\Druid\Aggregations\MinAggregator;
use Level23\Druid\Aggregations\SumAggregator;
use Level23\Druid\DruidClient;
use Level23\Druid\Filters\FilterBuilder;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\Queries\QueryBuilder;
use Mockery;
use tests\TestCase;

class HasAggregationsTest extends TestCase
{
    /**
     * @var \Level23\Druid\DruidClient
     */
    protected $client;

    /**
     * @var \Level23\Druid\Queries\QueryBuilder|\Mockery\MockInterface|\Mockery\LegacyMockInterface
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

    protected function filteredAggregatorTest($givenClosureOrNull)
    {
        $this->builder->shouldAllowMockingProtectedMethods()
            ->shouldReceive('buildFilteredAggregation')
            ->andReturnUsing(function ($aggregator, $closureOrNull) use ($givenClosureOrNull) {
                $this->assertIsObject($aggregator);
                $this->assertEquals($givenClosureOrNull, $closureOrNull);

                return $aggregator;
            });
    }

    public function testBuildFilteredAggregation()
    {
        $aggregation = new SumAggregator('age');

        $filter  = new SelectorFilter('name', 'john');
        $counter = 0;

        $closure = function (FilterBuilder $builder) use (&$counter, $filter) {

            $counter++;
            $builder->where($filter);
        };

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->builder->shouldAllowMockingProtectedMethods()->buildFilteredAggregation(
            $aggregation,
            $closure
        );

        $this->assertInstanceOf(FilteredAggregator::class, $response);

        /** @var FilteredAggregator $response */
        $this->assertEquals([
            'type'       => 'filtered',
            'filter'     => $filter->toArray(),
            'aggregator' => $aggregation->toArray(),
        ],
            $response->toArray()
        );

        $this->assertEquals(1, $counter);
    }

    public function testBuildFilteredAggregationWithoutFilter()
    {
        $aggregation = new SumAggregator('age');

        $counter = 0;

        $closure = function () use (&$counter) {
            $counter++;
            // @note: nothing happens here. By design.
        };

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->builder->shouldAllowMockingProtectedMethods()->buildFilteredAggregation(
            $aggregation,
            $closure
        );

        $this->assertEquals($aggregation, $response);
        $this->assertEquals(1, $counter);
    }

    /**
     * @param string $class
     *
     * @return \Mockery\Generator\MockConfigurationBuilder|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected function getAggregationMock(string $class)
    {
        $builder = new Mockery\Generator\MockConfigurationBuilder();
        $builder->setInstanceMock(true);
        $builder->setName($class);
        $builder->addTarget(AggregatorInterface::class);

        return Mockery::mock($builder);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testJavascript()
    {
        $this->getAggregationMock(JavascriptAggregator::class)
            ->shouldReceive('__construct')
            ->with(['name', 'age'], 'messages', 'a', 'b', 'c')
            ->once();

        $this->filteredAggregatorTest(null);

        $response = $this->builder->javascript('messages', ['name', 'age'], 'a', 'b', 'c');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSum()
    {
        $this->getAggregationMock(SumAggregator::class)
            ->shouldReceive('__construct')
            ->with('messages', 'nrOfMessages', 'float')
            ->once();

        $closure = function (FilterBuilder $builder) {
        };

        $this->filteredAggregatorTest($closure);

        $response = $this->builder->sum('messages', 'nrOfMessages', 'float', $closure);
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSumDefaults()
    {
        $this->getAggregationMock(SumAggregator::class)
            ->shouldReceive('__construct')
            ->with('messages', '', 'long')
            ->once();

        $response = $this->builder->sum('messages');
        $this->assertEquals($this->builder, $response);
    }

    public function testLongSum()
    {
        $this->builder->shouldReceive('sum')
            ->with('messages', '', 'long', null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->longSum('messages');
        $this->assertEquals($this->builder, $response);
    }

    public function testDoubleSum()
    {
        $this->builder->shouldReceive('sum')
            ->with('messages', '', 'double', null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->doubleSum('messages');
        $this->assertEquals($this->builder, $response);
    }

    public function testFloatSum()
    {
        $this->builder->shouldReceive('sum')
            ->with('messages', '', 'float', null)
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
        $this->getAggregationMock(CountAggregator::class)
            ->shouldReceive('__construct')
            ->with('totals')
            ->once();

        $this->filteredAggregatorTest(null);

        $response = $this->builder->count('totals');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDistinctCount()
    {
        $this->getAggregationMock(DistinctCountAggregator::class)
            ->shouldReceive('__construct')
            ->with('message_id', 'messageIds', 32768)
            ->once();

        $closure = function (FilterBuilder $builder) {
        };

        $this->filteredAggregatorTest($closure);

        $response = $this->builder->distinctCount('message_id', 'messageIds', 32768, $closure);
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDistinctCountDefaults()
    {
        $this->getAggregationMock(DistinctCountAggregator::class)
            ->shouldReceive('__construct')
            ->with('message_id', 'message_id', 16384)
            ->once();

        $this->filteredAggregatorTest(null);

        $response = $this->builder->distinctCount('message_id');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMin()
    {
        $this->getAggregationMock(MinAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', 'minAge', 'float')
            ->once();

        $closure = function (FilterBuilder $builder) {
        };

        $this->filteredAggregatorTest($closure);

        $response = $this->builder->min('age', 'minAge', 'float', $closure);
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMinDefaults()
    {
        $this->getAggregationMock(MinAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', '', 'long')
            ->once();

        $this->filteredAggregatorTest(null);

        $response = $this->builder->min('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testLongMin()
    {
        $this->builder->shouldReceive('min')
            ->with('age', '', 'long', null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->longMin('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testDoubleMin()
    {
        $this->builder->shouldReceive('min')
            ->with('age', '', 'double', null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->doubleMin('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testFloatMin()
    {
        $this->builder->shouldReceive('min')
            ->with('age', '', 'float', null)
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
        $this->getAggregationMock(MaxAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', 'maxAge', 'float')
            ->once();

        $closure = function (FilterBuilder $builder) {
        };

        $this->filteredAggregatorTest($closure);

        $response = $this->builder->max('age', 'maxAge', 'float', $closure);
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMaxDefaults()
    {
        $this->getAggregationMock(MaxAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', '', 'long')
            ->once();

        $this->filteredAggregatorTest(null);

        $response = $this->builder->max('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testLongMax()
    {
        $this->builder->shouldReceive('max')
            ->with('age', '', 'long', null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->longMax('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testDoubleMax()
    {
        $this->builder->shouldReceive('max')
            ->with('age', '', 'double', null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->doubleMax('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testFloatMax()
    {
        $this->builder->shouldReceive('max')
            ->with('age', '', 'float', null)
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
        $this->getAggregationMock(FirstAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', 'firstAge', 'float')
            ->once();

        $closure = function (FilterBuilder $builder) {
        };

        $this->filteredAggregatorTest($closure);

        $response = $this->builder->first('age', 'firstAge', 'float', $closure);
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFirstDefaults()
    {
        $this->getAggregationMock(FirstAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', '', 'long')
            ->once();

        $this->filteredAggregatorTest(null);

        $response = $this->builder->first('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testLongFirst()
    {
        $this->builder->shouldReceive('first')
            ->with('age', '', 'long', null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->longFirst('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testFloatFirst()
    {
        $this->builder->shouldReceive('first')
            ->with('age', '', 'float', null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->floatFirst('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testDoubleFirst()
    {
        $this->builder->shouldReceive('first')
            ->with('age', '', 'double', null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->doubleFirst('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testStringFirst()
    {
        $this->builder->shouldReceive('first')
            ->with('age', '', 'string', null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->stringFirst('age');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLast()
    {
        $this->getAggregationMock(LastAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', 'lastAge', 'float')
            ->once();

        $closure = function (FilterBuilder $builder) {
        };

        $this->filteredAggregatorTest($closure);

        $response = $this->builder->last('age', 'lastAge', 'float', $closure);
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLastDefaults()
    {
        $this->getAggregationMock(LastAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', '', 'long')
            ->once();

        $this->filteredAggregatorTest(null);

        $response = $this->builder->last('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testLongLast()
    {
        $this->builder->shouldReceive('last')
            ->with('age', '', 'long', null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->longLast('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testFloatLast()
    {
        $this->builder->shouldReceive('last')
            ->with('age', '', 'float', null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->floatLast('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testDoubleLast()
    {
        $this->builder->shouldReceive('last')
            ->with('age', '', 'double', null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->doubleLast('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testStringLast()
    {
        $this->builder->shouldReceive('last')
            ->with('age', '', 'string', null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->stringLast('age');
        $this->assertEquals($this->builder, $response);
    }
}