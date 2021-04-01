<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Concerns;

use Mockery;
use Exception;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\LikeFilter;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\HavingFilters\HavingBuilder;
use Level23\Druid\HavingFilters\OrHavingFilter;
use Level23\Druid\HavingFilters\AndHavingFilter;
use Level23\Druid\HavingFilters\QueryHavingFilter;
use Level23\Druid\HavingFilters\HavingFilterInterface;
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
            ['id', '0', null, 'and'],
            ['age', '<>', '12', 'and'],
            ['age', '>', '18', 'and'],
            ['age', '>=', '18', 'and'],
            ['age', '<', '18', 'and'],
            ['age', '<=', '18', 'and'],
            ['name', 'LiKE', 'John%', 'and'],
            ['name', 'NoT LiKE', 'John%', 'and'],
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
    public function testHaving($field, $operator, $value, $boolean): void
    {
        if ($value === null && $operator !== null) {
            $testingValue    = $operator;
            $testingOperator = '=';
        } else {
            $testingOperator = strtolower($operator);
            $testingValue    = $value;
        }

        $expected = null;

        switch ($testingOperator) {
            case '<>':
            case '!=':
                $expected = [
                    'type'       => 'not',
                    'havingSpec' =>
                        [
                            'type'      => 'dimSelector',
                            'dimension' => $field,
                            'value'     => $testingValue,
                        ],
                ];
                break;

            case '>=':
                $expected = [
                    'type'        => 'or',
                    'havingSpecs' =>
                        [
                            0 =>
                                [
                                    'type'        => 'greaterThan',
                                    'aggregation' => $field,
                                    'value'       => $testingValue,
                                ],
                            1 =>
                                [
                                    'type'        => 'equalTo',
                                    'aggregation' => $field,
                                    'value'       => $testingValue,
                                ],
                        ],
                ];
                break;

            case '<=':
                $expected = [
                    'type'        => 'or',
                    'havingSpecs' =>
                        [
                            0 =>
                                [
                                    'type'        => 'lessThan',
                                    'aggregation' => $field,
                                    'value'       => $testingValue,
                                ],
                            1 =>
                                [
                                    'type'        => 'equalTo',
                                    'aggregation' => $field,
                                    'value'       => $testingValue,
                                ],
                        ],
                ];
                break;

            case 'like':
                $expected = [
                    'type'   => 'filter',
                    'filter' =>
                        [
                            'type'      => 'like',
                            'dimension' => $field,
                            'pattern'   => $testingValue,
                            'escape'    => '\\',
                        ],
                ];
                break;

            case 'not like':
                $expected = [
                    'type'       => 'not',
                    'havingSpec' =>
                        [
                            'type'   => 'filter',
                            'filter' =>
                                [
                                    'type'      => 'like',
                                    'dimension' => $field,
                                    'pattern'   => $testingValue,
                                    'escape'    => '\\',
                                ],
                        ],
                ];
                break;

            case '=':
                $expected = [
                    'type'      => 'dimSelector',
                    'dimension' => $field,
                    'value'     => $testingValue,
                ];
                break;

            case '<':
                $expected = [
                    'type'        => 'lessThan',
                    'aggregation' => $field,
                    'value'       => $testingValue,
                ];
                break;

            case '>':
                $expected = [
                    'type'        => 'greaterThan',
                    'aggregation' => $field,
                    'value'       => $testingValue,
                ];
                break;

            default:
                throw new Exception('Unknown operator ' . $testingOperator);
        }

        $response = $this->builder->having($field, $operator, $value, $boolean);
        $this->assertEquals($this->builder, $response);

        $having = $this->builder->getHaving();
        if ($having instanceof HavingFilterInterface) {
            $this->assertEquals($expected, $having->toArray());
        }

        // add another
        $this->builder->having($field, $operator, $value, $boolean);

        if (strtolower($boolean) == 'and') {
            $this->assertInstanceOf(AndHavingFilter::class, $this->builder->getHaving());
        } else {
            $this->assertInstanceOf(OrHavingFilter::class, $this->builder->getHaving());
        }
    }

    public function testHavingMultipleAnd(): void
    {
        $this->builder->having('name', '!=', 'John', 'AnD');
        $this->builder->having('name', '!=', 'Doe', 'AnD');
        $this->builder->having('name', '!=', 'Jane', 'AnD');

        $filter = $this->builder->getHaving();
        if ($filter != null) {
            $this->assertEquals(AndHavingFilter::class, get_class($filter));
        }

        if ($filter instanceof AndHavingFilter) {
            $this->assertCount(3, $filter->toArray()['havingSpecs']);
        }
    }

    public function testHavingMultipleOr(): void
    {
        $this->builder->having('name', '!=', 'John', 'Or');
        $this->builder->having('name', '!=', 'Doe', 'OR');
        $this->builder->having('name', '!=', 'Jane', 'OR');

        $filter = $this->builder->getHaving();
        if ($filter != null) {
            $this->assertEquals(OrHavingFilter::class, get_class($filter));
        }
        if ($filter instanceof OrHavingFilter) {
            $this->assertCount(3, $filter->toArray()['havingSpecs']);
        }
    }

    /**
     * @testWith [null, null, "name"]
     *           [null, null, null]
     *           [null, "=", null]
     *           [null, "=", "name"]
     *
     * @param string|null $field
     * @param string|null $operator
     * @param string|null $value
     */
    public function testInvalidArguments(?string $field, ?string $operator, ?string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->builder->having($field, $operator, $value);
    }

    public function testHavingWithQueryFilter(): void
    {
        $like = new LikeFilter('name', 'aap%');

        $this->builder->having($like);
        $this->assertInstanceOf(QueryHavingFilter::class, $this->builder->getHaving());
    }

    public function testWithHavingFilterObject(): void
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
            $this->assertCount(3, $having->getHavingFilters());
        }
    }

    public function testWHavingClosure(): void
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
    public function testOrHaving(): void
    {
        $this->builder->shouldReceive('having')
            ->with('name', '=', 'John', 'or')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->orHaving('name', '=', 'John');

        $this->assertEquals($this->builder, $response);
    }
}
