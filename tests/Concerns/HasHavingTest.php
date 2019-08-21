<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Concerns;

use Mockery;
use Exception;
use tests\TestCase;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Filters\NotFilter;
use Level23\Druid\Filters\LikeFilter;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\HavingFilters\HavingBuilder;
use Level23\Druid\HavingFilters\OrHavingFilter;
use Level23\Druid\HavingFilters\AndHavingFilter;
use Level23\Druid\HavingFilters\NotHavingFilter;
use Level23\Druid\HavingFilters\QueryHavingFilter;
use Level23\Druid\HavingFilters\EqualToHavingFilter;
use Level23\Druid\HavingFilters\LessThanHavingFilter;
use Level23\Druid\HavingFilters\HavingFilterInterface;
use Level23\Druid\HavingFilters\GreaterThanHavingFilter;
use Level23\Druid\HavingFilters\DimensionSelectorHavingFilter;

class HasHavingTest extends TestCase
{
    /**
     * @var \Level23\Druid\DruidClient
     */
    protected $client;

    /**
     * @var \Level23\Druid\Queries\QueryBuilder|\Mockery\MockInterface|\Mockery\LegacyMockInterface
     */
    protected $builder;

    public function setUp(): void
    {
        $this->client  = new DruidClient([]);
        $this->builder = Mockery::mock(QueryBuilder::class, [$this->client, 'http://']);
        $this->builder->makePartial();
    }

    /**
     * @param string $class
     *
     * @return \Mockery\Generator\MockConfigurationBuilder|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected function getFilterMock(string $class)
    {
        $builder = new Mockery\Generator\MockConfigurationBuilder();
        $builder->setInstanceMock(true);
        $builder->setName($class);
        $builder->addTarget(FilterInterface::class);

        return Mockery::mock($builder);
    }

    /**
     * @param string $class
     *
     * @return \Mockery\Generator\MockConfigurationBuilder|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected function getHavingMock(string $class)
    {
        $builder = new Mockery\Generator\MockConfigurationBuilder();
        $builder->setInstanceMock(true);
        $builder->setName($class);
        $builder->addTarget(HavingFilterInterface::class);

        return Mockery::mock($builder);
    }

    public function whereDataProvider(): array
    {
        return [
            ['name', '=', 'John', 'and'],
            ['name', 'John', null, 'and'],
            ['age', '!=', '11', 'and'],
            ['age', '<>', '12', 'and'],
            ['age', '>', '18', 'and'],
            ['age', '>=', '18', 'and'],
            ['age', '<', '18', 'and'],
            ['age', '<=', '18', 'and'],
            ['name', 'LiKE', 'John%', 'and'],
        ];
    }

    /**
     * @dataProvider        whereDataProvider
     *
     * @param string      $field
     * @param string      $operator
     * @param string|null $value
     * @param string      $boolean
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws \Exception
     */
    public function testHaving($field, $operator, $value, $boolean)
    {
        if ($value === null && !empty($operator)) {
            $testingValue    = $operator;
            $testingOperator = '=';
        } else {
            $testingOperator = strtolower($operator);
            $testingValue    = $value;
        }

        switch ($testingOperator) {
            case '<>':
            case '!=':
                $class = NotHavingFilter::class;
                $this->getFilterMock(NotFilter::class)
                    ->shouldReceive('__construct')
                    ->once();

                $this->getHavingMock(DimensionSelectorHavingFilter::class)
                    ->shouldReceive('__construct')
                    ->with($field, $testingValue)
                    ->once();

                break;

            case '>=':
                $class = OrHavingFilter::class;
                $this->getHavingMock(GreaterThanHavingFilter::class)
                    ->shouldReceive('__construct')
                    ->with($field, floatval($testingValue))
                    ->once();

                $this->getHavingMock(EqualToHavingFilter::class)
                    ->shouldReceive('__construct')
                    ->with($field, floatval($testingValue))
                    ->once();
                break;

            case '<=':
                $class = OrHavingFilter::class;
                $this->getHavingMock(LessThanHavingFilter::class)
                    ->shouldReceive('__construct')
                    ->with($field, floatval($testingValue))
                    ->once();

                $this->getHavingMock(EqualToHavingFilter::class)
                    ->shouldReceive('__construct')
                    ->with($field, floatval($testingValue))
                    ->once();
                break;

            case 'like':
                $class = QueryHavingFilter::class;
                $this->getHavingMock(QueryHavingFilter::class)
                    ->shouldReceive('__construct')
                    ->once();

                $this->getFilterMock(LikeFilter::class)
                    ->shouldReceive('__construct')
                    ->with($field, $testingValue)
                    ->once();
                break;

            default:
                $types = [
                    '=' => DimensionSelectorHavingFilter::class,
                    '>' => GreaterThanHavingFilter::class,
                    '<' => LessThanHavingFilter::class,
                ];

                if (!array_key_exists($testingOperator, $types)) {
                    throw new Exception('Unknown operator ' . $testingOperator);
                }

                $class = $types[$testingOperator];

                $this->getHavingMock($class)
                    ->shouldReceive('__construct')
                    ->with($field, $testingValue)
                    ->once();
                break;
        }

        $response = $this->builder->having($field, $operator, $value, $boolean);
        $this->assertEquals($this->builder, $response);

        $this->assertInstanceOf($class, $this->builder->getHaving());

        // add another
        $this->builder->having($field, $operator, $value, $boolean);

        if (strtolower($boolean) == 'and') {
            $this->assertInstanceOf(AndHavingFilter::class, $this->builder->getHaving());
        } else {
            $this->assertInstanceOf(OrHavingFilter::class, $this->builder->getHaving());
        }
    }

    public function testInvalidArguments()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->builder->having(null);
    }

    public function testHavingWithQueryFilter()
    {
        $like = new LikeFilter('name', 'aap%');

        $this->builder->having($like);
        $this->assertInstanceOf(QueryHavingFilter::class, $this->builder->getHaving());
    }

    public function testWithHavingFilterObject()
    {
        $having = new DimensionSelectorHavingFilter('name', 'John');

        $this->builder->having($having);
        $this->assertEquals($this->builder->getHaving(), $having);

        $this->builder->having($having);
        $response = $this->builder->having($having);
        $this->assertEquals($this->builder, $response);

        $this->assertInstanceOf(AndHavingFilter::class, $this->builder->getHaving());

        $having = $this->builder->getHaving();
        if ($having instanceof AndHavingFilter) {
            $this->assertEquals(3, count($having->getHavingFilters()));
        }
    }

    public function testWHavingClosure()
    {
        $filter = new DimensionSelectorHavingFilter('name', 'John');

        $counter  = 0;
        $response = $this->builder->having(function (HavingBuilder $builder) use (&$counter, $filter) {
            $counter++;
            $builder->having($filter);
        });

        $this->assertEquals($this->builder->getHaving(), $filter);
        $this->assertEquals(1, $counter);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the orWhere method.
     */
    public function testOrHaving()
    {
        $this->builder->shouldReceive('having')
            ->with('name', '=', 'John', 'or')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->orHaving('name', '=', 'John');

        $this->assertEquals($this->builder, $response);
    }
}