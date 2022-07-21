<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Concerns;

use Mockery;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Hamcrest\Core\IsInstanceOf;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\PostAggregations\CdfPostAggregator;
use Level23\Druid\PostAggregations\RankPostAggregator;
use Level23\Druid\PostAggregations\LeastPostAggregator;
use Level23\Druid\Collections\PostAggregationCollection;
use Level23\Druid\PostAggregations\ConstantPostAggregator;
use Level23\Druid\PostAggregations\GreatestPostAggregator;
use Level23\Druid\PostAggregations\QuantilePostAggregator;
use Level23\Druid\PostAggregations\PostAggregatorInterface;
use Level23\Druid\PostAggregations\PostAggregationsBuilder;
use Level23\Druid\PostAggregations\QuantilesPostAggregator;
use Level23\Druid\PostAggregations\HistogramPostAggregator;
use Level23\Druid\PostAggregations\ArithmeticPostAggregator;
use Level23\Druid\PostAggregations\JavaScriptPostAggregator;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;
use Level23\Druid\PostAggregations\SketchSummaryPostAggregator;
use Level23\Druid\PostAggregations\HyperUniqueCardinalityPostAggregator;

class HasPostAggregationsTest extends TestCase
{
    /**
     * @var \Level23\Druid\Queries\QueryBuilder|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $builder;

    public function setUp(): void
    {
        $client        = new DruidClient([]);
        $this->builder = Mockery::mock(QueryBuilder::class, [$client, 'dataSource']);
        $this->builder->makePartial();
    }

    public function testBuildFields(): void
    {
        $fields = [
            'field',
            new HyperUniqueCardinalityPostAggregator(
                'myHyperUniqueField',
                'myHyperUniqueCardinality'
            ),
            function (PostAggregationsBuilder $builder) {
                $builder->constant(3.14, 'pi');
            },
        ];

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->builder->shouldAllowMockingProtectedMethods()->buildFields($fields);

        $this->assertInstanceOf(PostAggregationCollection::class, $response);

        if ($response instanceof PostAggregationCollection) {
            $this->assertEquals([
                [
                    'type'      => 'fieldAccess',
                    'name'      => 'field',
                    'fieldName' => 'field',
                ],
                [
                    'type'      => 'hyperUniqueCardinality',
                    'name'      => 'myHyperUniqueCardinality',
                    'fieldName' => 'myHyperUniqueField',
                ],
                [
                    'type'  => 'constant',
                    'name'  => 'pi',
                    'value' => 3.14,
                ],
            ],
                $response->toArray()
            );
        }
    }

    public function testBuildFieldsWithIncorrectType(): void
    {
        $fields = [
            new Dimension('field'),
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Incorrect field type given in postAggregation fields');

        /** @noinspection PhpUndefinedMethodInspection */
        $this->builder->shouldAllowMockingProtectedMethods()->buildFields($fields);
    }

    /**
     * @param string $class
     *
     * @return \Mockery\Generator\MockConfigurationBuilder|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected function getPostAggregationMock(string $class)
    {
        $builder = new Mockery\Generator\MockConfigurationBuilder();
        $builder->setInstanceMock(true);
        $builder->setName($class);
        $builder->addTarget(PostAggregatorInterface::class);

        return Mockery::mock($builder);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDivide(): void
    {
        $fields = ['field1', 'field2'];

        $this->getPostAggregationMock(ArithmeticPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('myAverageField', '/', new IsInstanceOf(PostAggregationCollection::class), true);

        $result = $this->builder->divide('myAverageField', $fields);

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMultiply(): void
    {
        $fields = ['field1', 'field2'];

        $this->getPostAggregationMock(ArithmeticPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('myMultiplyField', '*', new IsInstanceOf(PostAggregationCollection::class), true);

        $result = $this->builder->multiply('myMultiplyField', $fields);

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSubtract(): void
    {
        $fields = ['field1', 'field2'];

        $this->getPostAggregationMock(ArithmeticPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('mySubtractField', '-', new IsInstanceOf(PostAggregationCollection::class), true);

        $result = $this->builder->subtract('mySubtractField', $fields);

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testAdd(): void
    {
        $fields = ['field1', 'field2'];

        $this->getPostAggregationMock(ArithmeticPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('myAddField', '+', new IsInstanceOf(PostAggregationCollection::class), true);

        $result = $this->builder->add('myAddField', $fields);

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testQuotient(): void
    {
        $fields = ['field1', 'field2'];

        $this->getPostAggregationMock(ArithmeticPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('myQuotientField', 'quotient', new IsInstanceOf(PostAggregationCollection::class), true);

        $result = $this->builder->quotient('myQuotientField', $fields);

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testQuantile(): void
    {
        $this->getPostAggregationMock(QuantilePostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->withArgs(function (PostAggregatorInterface $dimension, string $outputName, float $fraction) {
                $this->assertEquals([
                    'type'      => 'fieldAccess',
                    'name'      => 'sketchData',
                    'fieldName' => 'sketchData',
                ], $dimension->toArray());
                $this->assertEquals('percentile', $outputName);
                $this->assertEquals(0.95, $fraction);

                return true;
            });

        $result = $this->builder->quantile('percentile', 'sketchData', 0.95);

        $this->assertEquals($this->builder, $result);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You can only provide one post-aggregation, field access or constant as input field');

        $this->builder->quantile('percentile', function (PostAggregationsBuilder $builder) {
            $builder->fieldAccess('sketchData');
            $builder->constant(3, 'Three');
        }, 0.95);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testQuantiles(): void
    {
        $this->getPostAggregationMock(QuantilesPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->withArgs(function (PostAggregatorInterface $dimension, string $outputName, array $fractions) {
                $this->assertEquals([
                    'type'      => 'fieldAccess',
                    'name'      => 'sketchData',
                    'fieldName' => 'sketchData',
                ], $dimension->toArray());
                $this->assertEquals('percentiles', $outputName);
                $this->assertEquals([0.95, 1.10], $fractions);

                return true;
            });

        $result = $this->builder->quantiles('percentiles', 'sketchData', [0.95, 1.10]);

        $this->assertEquals($this->builder, $result);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You can only provide one post-aggregation, field access or constant as input field');

        $this->builder->quantiles('percentiles', function (PostAggregationsBuilder $builder) {
            $builder->fieldAccess('sketchData');
            $builder->constant(3, 'Three');
        }, [0.95, 1.10]);
    }

    /**
     * @testWith [null, null]
     *           [null, 10]
     *           [[1,2,3], null]
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param int[]|null $splitPoints
     * @param int|null   $numBins
     */
    public function testHistogram(?array $splitPoints, ?int $numBins): void
    {
        $this->getPostAggregationMock(HistogramPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->withArgs(function (PostAggregatorInterface $dimension, string $outputName, $mySplitPoints, $myNumBins) use
            (
                $splitPoints,
                $numBins
            ) {
                $this->assertEquals([
                    'type'      => 'fieldAccess',
                    'name'      => 'sketchData',
                    'fieldName' => 'sketchData',
                ], $dimension->toArray());
                $this->assertEquals('histogram', $outputName);
                $this->assertEquals($splitPoints, $mySplitPoints);
                $this->assertEquals($numBins, $myNumBins);

                return true;
            });

        $result = $this->builder->histogram('histogram', 'sketchData', $splitPoints, $numBins);

        $this->assertEquals($this->builder, $result);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You can only provide one post-aggregation, field access or constant as input field');

        $this->builder->histogram('histogram', function (PostAggregationsBuilder $builder) {
            $builder->fieldAccess('sketchData');
            $builder->constant(3, 'Three');
        }, $splitPoints, $numBins);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRank(): void
    {
        $this->getPostAggregationMock(RankPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->withArgs(function (PostAggregatorInterface $dimension, string $outputName, $value) {
                $this->assertEquals([
                    'type'      => 'fieldAccess',
                    'name'      => 'sketchData',
                    'fieldName' => 'sketchData',
                ], $dimension->toArray());
                $this->assertEquals('myRank', $outputName);
                $this->assertEquals(12, $value);

                return true;
            });

        $result = $this->builder->rank('myRank', 'sketchData', 12);

        $this->assertEquals($this->builder, $result);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You can only provide one post-aggregation, field access or constant as input field');

        $this->builder->rank('myRank', function (PostAggregationsBuilder $builder) {
            $builder->fieldAccess('sketchData');
            $builder->constant(3, 'Three');
        }, 12);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCdf(): void
    {
        $this->getPostAggregationMock(CdfPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->withArgs(function (PostAggregatorInterface $dimension, string $outputName, array $splitPoints) {
                $this->assertEquals([
                    'type'      => 'fieldAccess',
                    'name'      => 'sketchData',
                    'fieldName' => 'sketchData',
                ], $dimension->toArray());
                $this->assertEquals('cdfResult', $outputName);
                $this->assertEquals([1, 2, 3, 4, 5], $splitPoints);

                return true;
            });

        $result = $this->builder->cdf('cdfResult', 'sketchData', [1, 2, 3, 4, 5]);

        $this->assertEquals($this->builder, $result);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You can only provide one post-aggregation, field access or constant as input field');

        $this->builder->cdf('cdfResult', function (PostAggregationsBuilder $builder) {
            $builder->fieldAccess('sketchData');
            $builder->constant(3, 'Three');
        }, [1, 2, 3, 4, 5]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSketchSummary(): void
    {
        $this->getPostAggregationMock(SketchSummaryPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->withArgs(function (PostAggregatorInterface $dimension, string $outputName) {
                $this->assertEquals([
                    'type'      => 'fieldAccess',
                    'name'      => 'sketchData',
                    'fieldName' => 'sketchData',
                ], $dimension->toArray());
                $this->assertEquals('debug', $outputName);

                return true;
            });

        $result = $this->builder->sketchSummary('debug', 'sketchData');

        $this->assertEquals($this->builder, $result);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You can only provide one post-aggregation, field access or constant as input field');

        $this->builder->sketchSummary('debug', function (PostAggregationsBuilder $builder) {
            $builder->fieldAccess('sketchData');
            $builder->constant(3, 'Three');
        });
    }

    /**
     * @testWith [true]
     *           [false]
     *
     * @param bool $finalizing
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFieldAccess(bool $finalizing): void
    {
        $this->getPostAggregationMock(FieldAccessPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('myField', 'fooBar', $finalizing);

        $result = $this->builder->fieldAccess('myField', 'fooBar', $finalizing);

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFieldAccessDefaults(): void
    {
        $this->getPostAggregationMock(FieldAccessPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('myField', 'myField', false);

        $result = $this->builder->fieldAccess('myField');

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testConstant(): void
    {
        $this->getPostAggregationMock(ConstantPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('pi', 3.14);

        $result = $this->builder->constant(3.14, 'pi');

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLongGreatest(): void
    {
        $this->getPostAggregationMock(GreatestPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('theGreatest', new IsInstanceOf(PostAggregationCollection::class), 'long');

        $result = $this->builder->longGreatest('theGreatest', ['field1', 'field2']);

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDoubleGreatest(): void
    {
        $this->getPostAggregationMock(GreatestPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('theGreatest', new IsInstanceOf(PostAggregationCollection::class), 'double');

        $result = $this->builder->doubleGreatest('theGreatest', ['field1', 'field2']);

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLongLeast(): void
    {
        $this->getPostAggregationMock(LeastPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('theLeast', new IsInstanceOf(PostAggregationCollection::class), 'long');

        $result = $this->builder->longLeast('theLeast', ['field1', 'field2']);

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDoubleLeast(): void
    {
        $this->getPostAggregationMock(LeastPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('theLeast', new IsInstanceOf(PostAggregationCollection::class), 'double');

        $result = $this->builder->doubleLeast('theLeast', ['field1', 'field2']);

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testPostJavascript(): void
    {
        $jsFunction = 'function(a,b) { return a*b; }';

        $this->getPostAggregationMock(JavaScriptPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('myJsResult', new IsInstanceOf(PostAggregationCollection::class), $jsFunction);

        $result = $this->builder->postJavascript('myJsResult', $jsFunction, ['field1', 'field2']);

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testHyperUniqueCardinality(): void
    {
        $this->getPostAggregationMock(HyperUniqueCardinalityPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('myHyperUniqueField', 'myOutputName');

        $result = $this->builder->hyperUniqueCardinality('myHyperUniqueField', 'myOutputName');

        $this->assertEquals($this->builder, $result);
    }

    public function testGetPostAggregations(): void
    {
        $this->builder->constant(3.14, 'pi');

        $this->assertEquals([
            new ConstantPostAggregator('pi', 3.14),
        ], $this->builder->getPostAggregations());
    }
}
