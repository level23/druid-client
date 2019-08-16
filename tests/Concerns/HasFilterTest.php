<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Concerns;

use Exception;
use Hamcrest\Core\IsInstanceOf;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\FilterQueryBuilder;
use Level23\Druid\Filters\AndFilter;
use Level23\Druid\Filters\BoundFilter;
use Level23\Druid\Filters\InFilter;
use Level23\Druid\Filters\JavascriptFilter;
use Level23\Druid\Filters\LikeFilter;
use Level23\Druid\Filters\NotFilter;
use Level23\Druid\Filters\OrFilter;
use Level23\Druid\Filters\RegexFilter;
use Level23\Druid\Filters\SearchFilter;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\QueryBuilder;
use Mockery;
use tests\TestCase;

class HasFilterTest extends TestCase
{
    /**
     * @var \Level23\Druid\DruidClient
     */
    protected $client;

    /**
     * @var \Level23\Druid\QueryBuilder|\Mockery\MockInterface|\Mockery\LegacyMockInterface
     */
    protected $builder;

    public function setUp(): void
    {
        $this->client  = new DruidClient([]);
        $this->builder = Mockery::mock(QueryBuilder::class, [$this->client, 'http://']);
        $this->builder->makePartial();
    }

    public function whereDataProvider(): array
    {
        return [
            ['name', '=', 'John', 'and'],
            ['name', '=', 1, 'and'],
            ['name', 'John', null, 'and'],
            ['name', '!=', 'John', 'and'],
            ['name', '!=', 1, 'and'],
            ['name', '<>', 'John', 'AND'],
            ['age', '>', '18', 'and'],
            ['age', '>=', 18, 'and'],
            ['age', '<', '18', 'and'],
            ['age', '<=', '18', 'and'],
            ['name', 'LiKE', 'John%', 'and'],
            ['name', 'javaScript', 'function() { return "Piet"; }', 'and'],
            ['name', 'regex', '^[0-9]*$', 'and'],
            ['name', 'regexp', '^[0-9]*$', 'or'],
            ['name', 'search', ['john', 'doe'], 'aNd'],
            ['name', 'in', ['john', 'doe'], 'Or'],
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
    public function testWhere($field, $operator, $value, $boolean)
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
                $class = NotFilter::class;
                Mockery::mock('overload:' . NotFilter::class)
                    ->shouldReceive('__construct')
                    ->once();

                Mockery::mock('overload:' . SelectorFilter::class)
                    ->shouldReceive('__construct')
                    ->with($field, $testingValue)
                    ->once();

                break;

            case '>':
            case '>=':
            case '<':
            case'<=':
                $class = BoundFilter::class;
                Mockery::mock('overload:' . BoundFilter::class)
                    ->shouldReceive('__construct')
                    ->with($field, $testingOperator, (string)$testingValue)
                    ->once();
                break;

            default:
                $types = [
                    '='          => SelectorFilter::class,
                    'like'       => LikeFilter::class,
                    'javascript' => JavascriptFilter::class,
                    'regex'      => RegexFilter::class,
                    'regexp'     => RegexFilter::class,
                    'search'     => SearchFilter::class,
                    'in'         => InFilter::class,
                ];

                if (!array_key_exists($testingOperator, $types)) {
                    throw new Exception('Unknown operator ' . $testingOperator);
                }

                $class = $types[$testingOperator];

                Mockery::mock('overload:' . $class)
                    ->shouldReceive('__construct')
                    ->with($field, $testingValue)
                    ->once();
                break;
        }

        $response = $this->builder->where($field, $operator, $value, $boolean);
        $this->assertEquals($this->builder, $response);

        $this->assertInstanceOf($class, $this->builder->getFilter());

        // add another
        $this->builder->where($field, $operator, $value, $boolean);

        if (strtolower($boolean) == 'and') {
            $this->assertInstanceOf(AndFilter::class, $this->builder->getFilter());
        } else {
            $this->assertInstanceOf(OrFilter::class, $this->builder->getFilter());
        }
    }

    public function testWithFilterObject()
    {
        $where = new SelectorFilter('name', 'John');

        $this->builder->where($where);
        $this->assertEquals($this->builder->getFilter(), $where);

        $this->builder->where($where);
        $response = $this->builder->where($where);

        $this->assertInstanceOf(AndFilter::class, $this->builder->getFilter());

        $filter = $this->builder->getFilter();

        if ($filter instanceof AndFilter) {

            $filters = $filter->toArray();

            $this->assertEquals(3, count($filters['fields']));
        }

        $this->assertEquals($this->builder, $response);
    }

    public function testWhereClosure()
    {
        $where = new SelectorFilter('name', 'John');

        $counter  = 0;
        $response = $this->builder->where(function (FilterQueryBuilder $builder) use (&$counter, $where) {
            $counter++;
            $builder->where($where);
        });

        $this->assertEquals($this->builder->getFilter(), $where);
        $this->assertEquals(1, $counter);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the whereIn
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWhereIn()
    {
        $in = Mockery::mock('overload:' . InFilter::class);
        $in->shouldReceive('__construct')
            ->once()
            ->with('country_iso', ['nl', 'be']);

        $this->builder->shouldReceive('where')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->whereIn('country_iso', ['nl', 'be']);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the orWhere method.
     */
    public function testOrWhere()
    {
        $this->builder->shouldReceive('where')
            ->with('name', '=', 'John', 'or')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->orWhere('name', '=', 'John');

        $this->assertEquals($this->builder, $response);
    }

    public function testInvalidArguments()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->builder->where(null);
    }

    /**
     * Test the whereNotIn
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWhereNotIn()
    {
        $in = Mockery::mock('overload:' . InFilter::class);
        $in->shouldReceive('__construct')
            ->once()
            ->with('age', [16, 17, 18]);

        $not = Mockery::mock('overload:' . NotFilter::class);
        $not->shouldReceive('__construct')
            ->once()
            ->with(new IsInstanceOf(InFilter::class));

        $this->builder->shouldReceive('where')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->whereNotIn('age', [16, 17, 18]);

        $this->assertEquals($this->builder, $response);
    }
}