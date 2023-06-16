<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Concerns;

use Mockery;
use Closure;
use TypeError;
use Hamcrest\Core\IsEqual;
use Mockery\MockInterface;
use Level23\Druid\DruidClient;
use Hamcrest\Core\IsInstanceOf;
use Mockery\LegacyMockInterface;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\DataType;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Filters\FilterBuilder;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\Aggregations\MaxAggregator;
use Level23\Druid\Aggregations\MinAggregator;
use Level23\Druid\Aggregations\SumAggregator;
use Level23\Druid\Aggregations\AnyAggregator;
use Level23\Druid\Aggregations\LastAggregator;
use Level23\Druid\Dimensions\DimensionBuilder;
use Level23\Druid\Aggregations\CountAggregator;
use Level23\Druid\Aggregations\FirstAggregator;
use Level23\Druid\Extractions\ExtractionBuilder;
use Level23\Druid\Aggregations\FilteredAggregator;
use Level23\Druid\Collections\DimensionCollection;
use Level23\Druid\Aggregations\AggregatorInterface;
use Level23\Druid\Aggregations\JavascriptAggregator;
use Level23\Druid\Aggregations\HyperUniqueAggregator;
use Level23\Druid\Aggregations\CardinalityAggregator;
use Level23\Druid\Aggregations\DistinctCountAggregator;
use Level23\Druid\Aggregations\DoublesSketchAggregator;

class HasAggregationsTest extends TestCase
{
    protected DruidClient $client;


    protected QueryBuilder|MockInterface|LegacyMockInterface $builder;

    public function testGetAggregations(): void
    {
        $this->builder->sum('messages');
        $this->builder->first('age');

        $aggregations = $this->builder->getAggregations();

        $this->assertCount(2, $aggregations);

        $this->assertInstanceOf(SumAggregator::class, $aggregations[0]);
        $this->assertInstanceOf(FirstAggregator::class, $aggregations[1]);
    }

    public function setUp(): void
    {
        $this->client  = new DruidClient([]);
        $this->builder = Mockery::mock(QueryBuilder::class, [$this->client, 'http://']);
        $this->builder->makePartial();
    }

    protected function filteredAggregatorTest(?Closure $givenClosureOrNull): void
    {
        $this->builder->shouldAllowMockingProtectedMethods()
            ->shouldReceive('buildFilteredAggregation')
            ->andReturnUsing(function ($aggregator, $closureOrNull) use ($givenClosureOrNull) {
                $this->assertIsObject($aggregator);
                $this->assertEquals($givenClosureOrNull, $closureOrNull);

                return $aggregator;
            });
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @testWith [null, null]
     *           [256, null]
     *           [null, 1000000]
     *           [128, 1000000000]
     *
     * @param int|null $sizeAndAccuracy
     * @param int|null $maxStreamLength
     */
    public function testDoubleSketch(?int $sizeAndAccuracy, ?int $maxStreamLength): void
    {
        $this->getAggregationMock(DoublesSketchAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('myMetric', 'myOutput', $sizeAndAccuracy, $maxStreamLength);

        $result = $this->builder->doublesSketch('myMetric', 'myOutput', $sizeAndAccuracy, $maxStreamLength);

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDoubleSketchDefaults(): void
    {
        $this->getAggregationMock(DoublesSketchAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('myMetric', 'myMetric', null, null);

        $result = $this->builder->doublesSketch('myMetric');

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @testWith [true, true]
     *           [false, false]
     *           [true, false]
     *           [false, true]
     *
     * @param bool $round
     * @param bool $isInputHyperUnique
     */
    public function testHyperUnique(bool $round, bool $isInputHyperUnique): void
    {
        $this->getAggregationMock(HyperUniqueAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('myOutput', 'myMetric', $isInputHyperUnique, $round);

        $result = $this->builder->hyperUnique('myMetric', 'myOutput', $round, $isInputHyperUnique);

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testHyperUniqueDefaults(): void
    {
        $this->getAggregationMock(HyperUniqueAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('myOutput', 'myMetric', false, false);

        $result = $this->builder->hyperUnique('myMetric', 'myOutput');

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @testWith [true, true]
     *           [false, false]
     *           [true, false]
     *           [false, true]
     *
     * @param bool $byRow
     * @param bool $round
     */
    public function testCardinality(bool $byRow, bool $round): void
    {
        $this->getAggregationMock(CardinalityAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with(
                'distinct_last_name_first_char',
                new IsInstanceOf(DimensionCollection::class),
                $byRow,
                $round);

        $counter = 0;
        $closure = function (DimensionBuilder $builder) use (&$counter) {
            $counter++;
            $builder->select('last_name', 'last_name_first_char', function (ExtractionBuilder $extractionBuilder) {
                $extractionBuilder->substring(0, 1);
            });
        };

        $response = $this->builder->cardinality('distinct_last_name_first_char', $closure, $byRow, $round);

        $this->assertEquals($this->builder, $response);
        $this->assertEquals(1, $counter);
    }

    public function testCardinalityWithInvalidValue(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('must be of type Closure|array');

        /** @noinspection PhpParamsInspection */
        $this->builder->cardinality('distinct_last_name_first_char', 'hi');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCardinalityWithArray(): void
    {
        $dimensions = [new Dimension('last_name')];
        $this->getAggregationMock(CardinalityAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with(
                'distinct_last_name',
                new IsEqual(new DimensionCollection(...$dimensions)),
                true,
                false);

        $response = $this->builder->cardinality(
            'distinct_last_name', ['last_name'], true
        );

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCardinalityDefaults(): void
    {
        $this->getAggregationMock(CardinalityAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with(
                'distinct_last_name_first_char',
                new IsInstanceOf(DimensionCollection::class),
                false,
                false);

        $counter = 0;
        $closure = function (DimensionBuilder $builder) use (&$counter) {
            $counter++;
            $builder->select('last_name', 'last_name_first_char', function (ExtractionBuilder $extractionBuilder) {
                $extractionBuilder->substring(0, 1);
            });
        };

        $response = $this->builder->cardinality('distinct_last_name_first_char', $closure);

        $this->assertEquals($this->builder, $response);
        $this->assertEquals(1, $counter);
    }

    public function testBuildFilteredAggregation(): void
    {
        $aggregation = new SumAggregator('age');

        $filter  = new SelectorFilter('name', 'john');
        $counter = 0;

        $closure = function (FilterBuilder $builder) use (&$counter, $filter) {

            $counter++;
            $builder->where($filter);
        };

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

    public function testBuildFilteredAggregationWithoutFilter(): void
    {
        $aggregation = new SumAggregator('age');

        $counter = 0;

        $closure = function () use (&$counter) {
            $counter++;
            // @note: nothing happens here. By design.
        };

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
     * @return LegacyMockInterface|MockInterface
     */
    protected function getAggregationMock(string $class): LegacyMockInterface|MockInterface
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
    public function testJavascript(): void
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
    public function testSum(): void
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
    public function testSumDefaults(): void
    {
        $this->getAggregationMock(SumAggregator::class)
            ->shouldReceive('__construct')
            ->with('messages', '', DataType::LONG)
            ->once();

        $response = $this->builder->sum('messages');
        $this->assertEquals($this->builder, $response);
    }

    public function testLongSum(): void
    {
        $this->builder->shouldReceive('sum')
            ->with('messages', '', DataType::LONG, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->longSum('messages');
        $this->assertEquals($this->builder, $response);
    }

    public function testDoubleSum(): void
    {
        $this->builder->shouldReceive('sum')
            ->with('messages', '', DataType::DOUBLE, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->doubleSum('messages');
        $this->assertEquals($this->builder, $response);
    }

    public function testFloatSum(): void
    {
        $this->builder->shouldReceive('sum')
            ->with('messages', '', DataType::FLOAT, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->floatSum('messages');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCount(): void
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
    public function testDistinctCount(): void
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
    public function testDistinctCountDefaults(): void
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
    public function testMin(): void
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
    public function testMinDefaults(): void
    {
        $this->getAggregationMock(MinAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', '', DataType::LONG)
            ->once();

        $this->filteredAggregatorTest(null);

        $response = $this->builder->min('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testLongMin(): void
    {
        $this->builder->shouldReceive('min')
            ->with('age', '', DataType::LONG, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->longMin('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testDoubleMin(): void
    {
        $this->builder->shouldReceive('min')
            ->with('age', '', DataType::DOUBLE, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->doubleMin('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testFloatMin(): void
    {
        $this->builder->shouldReceive('min')
            ->with('age', '', DataType::FLOAT, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->floatMin('age');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMax(): void
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
    public function testMaxDefaults(): void
    {
        $this->getAggregationMock(MaxAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', '', DataType::LONG)
            ->once();

        $this->filteredAggregatorTest(null);

        $response = $this->builder->max('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testLongMax(): void
    {
        $this->builder->shouldReceive('max')
            ->with('age', '', DataType::LONG, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->longMax('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testDoubleMax(): void
    {
        $this->builder->shouldReceive('max')
            ->with('age', '', DataType::DOUBLE, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->doubleMax('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testFloatMax(): void
    {
        $this->builder->shouldReceive('max')
            ->with('age', '', DataType::FLOAT, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->floatMax('age');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFirst(): void
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
    public function testFirstDefaults(): void
    {
        $this->getAggregationMock(FirstAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', '', DataType::LONG)
            ->once();

        $this->filteredAggregatorTest(null);

        $response = $this->builder->first('age');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAny(): void
    {
        $this->getAggregationMock(AnyAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', 'anyAge', 'string', 2048)
            ->once();

        $closure = function (FilterBuilder $builder) {
        };

        $this->filteredAggregatorTest($closure);

        $response = $this->builder->any('age', 'anyAge', 'string', 2048, $closure);
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAnyDefaults(): void
    {
        $this->getAggregationMock(AnyAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', '', DataType::LONG, null)
            ->once();

        $this->filteredAggregatorTest(null);

        $response = $this->builder->any('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testLongAny(): void
    {
        $this->builder->shouldReceive('any')
            ->with('age', '', DataType::LONG, null, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->longAny('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testDoubleAny(): void
    {
        $this->builder->shouldReceive('any')
            ->with('age', '', DataType::DOUBLE, null, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->doubleAny('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testStringAny(): void
    {
        $this->builder->shouldReceive('any')
            ->with('age', '', DataType::STRING, null, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->stringAny('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testFloatAny(): void
    {
        $this->builder->shouldReceive('any')
            ->with('age', '', DataType::FLOAT, null, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->floatAny('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testLongFirst(): void
    {
        $this->builder->shouldReceive('first')
            ->with('age', '', DataType::LONG, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->longFirst('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testFloatFirst(): void
    {
        $this->builder->shouldReceive('first')
            ->with('age', '', DataType::FLOAT, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->floatFirst('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testDoubleFirst(): void
    {
        $this->builder->shouldReceive('first')
            ->with('age', '', DataType::DOUBLE, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->doubleFirst('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testStringFirst(): void
    {
        $this->builder->shouldReceive('first')
            ->with('age', '', DataType::STRING, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->stringFirst('age');
        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLast(): void
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
    public function testLastDefaults(): void
    {
        $this->getAggregationMock(LastAggregator::class)
            ->shouldReceive('__construct')
            ->with('age', '', DataType::LONG)
            ->once();

        $this->filteredAggregatorTest(null);

        $response = $this->builder->last('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testLongLast(): void
    {
        $this->builder->shouldReceive('last')
            ->with('age', '', DataType::LONG, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->longLast('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testFloatLast(): void
    {
        $this->builder->shouldReceive('last')
            ->with('age', '', DataType::FLOAT, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->floatLast('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testDoubleLast(): void
    {
        $this->builder->shouldReceive('last')
            ->with('age', '', DataType::DOUBLE, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->doubleLast('age');
        $this->assertEquals($this->builder, $response);
    }

    public function testStringLast(): void
    {
        $this->builder->shouldReceive('last')
            ->with('age', '', DataType::STRING, null)
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->stringLast('age');
        $this->assertEquals($this->builder, $response);
    }
}
