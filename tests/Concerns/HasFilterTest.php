<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Concerns;

use Mockery;
use DateTime;
use Exception;
use tests\TestCase;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Hamcrest\Core\IsInstanceOf;
use Level23\Druid\Filters\InFilter;
use Level23\Druid\Filters\OrFilter;
use Level23\Druid\Filters\AndFilter;
use Level23\Druid\Filters\NotFilter;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Filters\LikeFilter;
use Level23\Druid\Filters\BoundFilter;
use Level23\Druid\Filters\RegexFilter;
use Level23\Druid\Filters\SearchFilter;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\Filters\FilterBuilder;
use Level23\Druid\Filters\IntervalFilter;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Filters\JavascriptFilter;
use Level23\Druid\Extractions\ExtractionBuilder;

class HasFilterTest extends TestCase
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
            ['name', 'javaScript', 'function() { return "John"; }', 'and'],
            ['name', 'regex', '^[0-9]*$', 'and'],
            ['name', 'regexp', '^[0-9]*$', 'or'],
            ['name', 'search', ['john', 'doe'], 'aNd'],
            ['name', 'in', ['john', 'doe'], 'Or'],
        ];
    }

    public function normalizeIntervalsDataProvider(): array
    {
        return [
            [
                ['19-02-2019 00:00:00', '20-02-2019 00:00:00'],
                [new Interval('19-02-2019 00:00:00', '20-02-2019 00:00:00')],
            ],
            [
                [
                    '2019-04-15T08:00:00.000Z/2019-04-15T09:00:00.000Z',
                    '2019-03-15T08:00:00.000Z/2019-03-15T09:00:00.000Z',
                ],
                [
                    new Interval('2019-04-15T08:00:00.000Z/2019-04-15T09:00:00.000Z'),
                    new Interval('2019-03-15T08:00:00.000Z/2019-03-15T09:00:00.000Z'),
                ],
            ],
            [
                [new DateTime('19-02-2019 00:00:00'), new DateTime('20-02-2019 00:00:00')],
                [new Interval('19-02-2019 00:00:00', '20-02-2019 00:00:00')],
            ],
            [
                [($interval = new Interval('now', 'tomorrow'))],
                [$interval],
            ],
            [
                [['19-02-2019 00:00:00', '20-02-2019 00:00:00'], ['10-02-2019 00:00:00', '12-02-2019 00:00:00']],
                [
                    new Interval('19-02-2019 00:00:00', '20-02-2019 00:00:00'),
                    new Interval('10-02-2019 00:00:00', '12-02-2019 00:00:00'),
                ],
            ],
            [
                [
                    (new DateTime('19-02-2019 00:00:00'))->getTimestamp(),
                    (new DateTime('20-02-2019 00:00:00'))->getTimestamp(),
                ],
                [new Interval('19-02-2019 00:00:00', '20-02-2019 00:00:00')],
            ],
            [
                [null],
                [],
                InvalidArgumentException::class,
            ],
            [
                ['19-02-2019 00:00:00', '20-02-2019 00:00:00', '21-02-2019 00:00:00'],
                [],
                InvalidArgumentException::class,
            ],
            [
                [strtotime('19-02-2019 00:00:00'), strtotime('20-02-2019 00:00:00'), strtotime('21-02-2019 00:00:00')],
                [],
                InvalidArgumentException::class,
            ],
        ];
    }

    /**
     * @param array $given
     * @param array $expected
     *
     * @dataProvider normalizeIntervalsDataProvider
     */
    public function testNormalizeIntervals(array $given, array $expected, string $expectException = "")
    {
        if (!empty($expectException)) {
            $this->expectException($expectException);
        }
        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->builder->shouldAllowMockingProtectedMethods()->normalizeIntervals($given);

        $this->assertEquals($expected, $response);
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
                $this->getFilterMock(NotFilter::class)
                    ->shouldReceive('__construct')
                    ->once();

                $this->getFilterMock(SelectorFilter::class)
                    ->shouldReceive('__construct')
                    ->with($field, $testingValue, null)
                    ->once();

                break;

            case '>':
            case '>=':
            case '<':
            case'<=':
                $class = BoundFilter::class;
                $this->getFilterMock(BoundFilter::class)
                    ->shouldReceive('__construct')
                    ->with($field, $testingOperator, (string)$testingValue, null, null)
                    ->once();
                break;

            case 'like':
                $class = LikeFilter::class;
                $this->getFilterMock(LikeFilter::class)
                    ->shouldReceive('__construct')
                    ->with($field, (string)$testingValue, '\\', null)
                    ->once();
                break;

            case 'search':
                $class = SearchFilter::class;
                $this->getFilterMock(SearchFilter::class)
                    ->shouldReceive('__construct')
                    ->with($field, $testingValue, false, null)
                    ->once();
                break;

            default:
                $types = [
                    '='          => SelectorFilter::class,
                    'javascript' => JavascriptFilter::class,
                    'regex'      => RegexFilter::class,
                    'regexp'     => RegexFilter::class,
                    'in'         => InFilter::class,
                ];

                if (!array_key_exists($testingOperator, $types)) {
                    throw new Exception('Unknown operator ' . $testingOperator);
                }

                $class = $types[$testingOperator];

                $this->getFilterMock($class)
                    ->shouldReceive('__construct')
                    ->with($field, $testingValue, null)
                    ->once();
                break;
        }

        $response = $this->builder->where($field, $operator, $value, null, $boolean);
        $this->assertEquals($this->builder, $response);

        $this->assertInstanceOf($class, $this->builder->getFilter());

        // add another
        $this->builder->where($field, $operator, $value, null, $boolean);

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
        $response = $this->builder->where(function (FilterBuilder $builder) use (&$counter, $where) {
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
        $in = $this->getFilterMock(InFilter::class);
        $in->shouldReceive('__construct')
            ->once()
            ->with('country_iso', ['nl', 'be'], null);

        $this->builder->shouldReceive('where')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->whereIn('country_iso', ['nl', 'be']);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @throws \Exception
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWhereInterval()
    {
        $interval = new Interval('now', 'tomorrow');

        $filter = $this->getFilterMock(IntervalFilter::class);
        $filter->shouldReceive('__construct')
            ->once()
            ->andReturnUsing(function ($dimension, $intervals, $extraction) use ($interval) {
                $this->assertEquals('__time', $dimension);
                $this->assertIsArray($intervals);
                $this->assertEquals($interval, $intervals[0]);
                $this->assertNull($extraction);
            });

        $response = $this->builder->whereInterval('__time', [$interval->getStart(), $interval->getStop()], null);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the orWhere method.
     */
    public function testOrWhere()
    {
        $this->builder->shouldReceive('where')
            ->with('name', '=', 'John', null, 'or')
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
        $in = $this->getFilterMock(InFilter::class);
        $in->shouldReceive('__construct')
            ->once()
            ->with('age', [16, 17, 18], null);

        $not = $this->getFilterMock(NotFilter::class);
        $not->shouldReceive('__construct')
            ->once()
            ->with(new IsInstanceOf(InFilter::class));

        $this->builder->shouldReceive('where')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->whereNotIn('age', [16, 17, 18]);

        $this->assertEquals($this->builder, $response);
    }

    public function testWhereUsingExtraction()
    {
        $counter = 0;
        $this->builder->whereIn('user_id', ['bob', 'john'], function (ExtractionBuilder $builder) use (&$counter) {
            $counter++;
            $builder->lookup('username', false);
        });

        $this->assertEquals(1, $counter);
    }
}