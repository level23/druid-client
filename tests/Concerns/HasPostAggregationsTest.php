<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Concerns;

use Mockery;
use tests\TestCase;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Hamcrest\Core\IsInstanceOf;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\PostAggregations\LeastPostAggregator;
use Level23\Druid\Collections\PostAggregationCollection;
use Level23\Druid\PostAggregations\ConstantPostAggregator;
use Level23\Druid\PostAggregations\GreatestPostAggregator;
use Level23\Druid\PostAggregations\PostAggregatorInterface;
use Level23\Druid\PostAggregations\PostAggregationsBuilder;
use Level23\Druid\PostAggregations\ArithmeticPostAggregator;
use Level23\Druid\PostAggregations\JavaScriptPostAggregator;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;
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

    public function testBuildFields()
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

    public function testBuildFieldsWithIncorrectType()
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
    public function testDivide()
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
    public function testMultiply()
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
    public function testSubtract()
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
    public function testAdd()
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
    public function testQuotient()
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
     * @testWith [true]
     *           [false]
     *
     * @param bool $finalizing
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFieldAccess(bool $finalizing)
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
    public function testFieldAccessDefaults()
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
    public function testConstant()
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
    public function testLongGreatest()
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
    public function testDoubleGreatest()
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
    public function testLongLeast()
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
    public function testDoubleLeast()
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
    public function testPostJavascript()
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
    public function testHyperUniqueCardinality()
    {
        $this->getPostAggregationMock(HyperUniqueCardinalityPostAggregator::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('myHyperUniqueField', 'myOutputName');

        $result = $this->builder->hyperUniqueCardinality( 'myHyperUniqueField', 'myOutputName');

        $this->assertEquals($this->builder, $result);
    }

    public function testGetPostAggregations()
    {
        $this->builder->constant(3.14, 'pi');

        $this->assertEquals([
            new ConstantPostAggregator('pi', 3.14),
        ], $this->builder->getPostAggregations());
    }
}
