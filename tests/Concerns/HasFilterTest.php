<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Concerns;

use Mockery;
use Closure;
use DateTime;
use Exception;
use Hamcrest\Core\IsEqual;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Hamcrest\Core\IsInstanceOf;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\DataType;
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
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Filters\FilterBuilder;
use Level23\Druid\Filters\BetweenFilter;
use Level23\Druid\Filters\IntervalFilter;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Filters\JavascriptFilter;
use Level23\Druid\Dimensions\DimensionBuilder;
use Level23\Druid\VirtualColumns\VirtualColumn;
use Level23\Druid\Extractions\ExtractionBuilder;
use Level23\Druid\Filters\ColumnComparisonFilter;
use Level23\Druid\Extractions\SubstringExtraction;
use Level23\Druid\Filters\LogicalExpressionFilterInterface;

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
            ['id', '0', null, 'and'],
            ['name', '!=', 'John', 'and'],
            ['name', '!=', 1, 'and'],
            ['name', '<>', 'John', 'AND'],
            ['age', '>', '18', 'and'],
            ['age', '>=', 18, 'and'],
            ['age', '<', '18', 'and'],
            ['age', '<=', '18', 'and'],
            ['name', 'LiKE', 'John%', 'and'],
            ['name', 'NoT LiKE', 'Jack%', 'and'],
            ['name', 'javaScript', 'function() { return "John"; }', 'and'],
            ['name', 'NOT javaScript', 'function() { return false; }', 'OR'],
            ['name', 'regex', '^[0-9]*$', 'and'],
            ['name', 'NOT regex', '^[0-9]*$', 'and'],
            ['name', 'regexp', '^[0-9]*$', 'oR'],
            ['name', 'NOT regexp', '^[0-9]*$', 'oR'],
            ['name', 'search', ['john', 'doe'], 'aNd'],
            ['name', 'not search', ['john', 'doe'], 'aNd'],
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
     * @param array  $given
     * @param array  $expected
     * @param string $expectException
     *
     * @dataProvider normalizeIntervalsDataProvider
     */
    public function testNormalizeIntervals(array $given, array $expected, string $expectException = ""): void
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
     * @param string $field
     * @param string $operator
     * @param mixed  $value
     * @param string $boolean
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws \Exception
     */
    public function testWhere(string $field, string $operator, $value, string $boolean): void
    {
        if ($value === null && $operator !== null) {
            $testingValue    = $operator;
            $testingOperator = '=';
        } else {
            $testingOperator = strtolower($operator);
            $testingValue    = $value;
        }

        $not = false;
        if ($operator == '!=' || $operator == '<>' || substr($testingOperator, 0, 3) == 'not') {

            if (substr($testingOperator, 0, 3) == 'not') {
                $testingOperator = substr($testingOperator, 4);
            }
            $not = true;

            $this->getFilterMock(NotFilter::class)
                ->shouldReceive('__construct')
                ->once();
        }

        switch ($testingOperator) {
            case '<>':
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
                    '!='         => SelectorFilter::class,
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

        if ($not) {
            $this->assertInstanceOf(NotFilter::class, $this->builder->getFilter());
        } else {
            $this->assertInstanceOf($class, $this->builder->getFilter());
        }

        // add another
        $this->builder->where($field, $operator, $value, null, $boolean);

        if (strtolower($boolean) == 'and') {
            $this->assertInstanceOf(AndFilter::class, $this->builder->getFilter());
        } else {
            $this->assertInstanceOf(OrFilter::class, $this->builder->getFilter());
        }
    }

    public function testWhereMultipleAnd(): void
    {
        $this->builder->where('name', '!=', 'John', null, 'AnD');
        $this->builder->where('name', '!=', 'Doe', null, 'AnD');
        $this->builder->where('name', '!=', 'Jane', null, 'AnD');

        $filter = $this->builder->getFilter();
        if ($filter instanceof LogicalExpressionFilterInterface) {
            $this->assertEquals(AndFilter::class, get_class($filter));
            $this->assertCount(3, $filter->toArray()['fields']);
        }
    }

    public function testWhereMultipleOr(): void
    {
        $this->builder->where('name', '!=', 'John', null, 'Or');
        $this->builder->where('name', '!=', 'Doe', null, 'OR');
        $this->builder->where('name', '!=', 'Jane', null, 'OR');

        $filter = $this->builder->getFilter();
        if ($filter instanceof LogicalExpressionFilterInterface) {
            $this->assertEquals(OrFilter::class, get_class($filter));
            $this->assertCount(3, $filter->toArray()['fields']);
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
    public function testWhereInvalidArguments(?string $field, ?string $operator, ?string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->builder->where($field, $operator, $value);
    }

    /**
     * @testWith [true, false]
     *           [true, true]
     *
     * @param bool $withoutOperator
     * @param bool $withoutValue
     */
    public function testWhereWithoutOperatorOrValue(bool $withoutOperator, bool $withoutValue): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to supply an operator and an compare value when you supply a dimension as string');
        $this->builder->where(
            'field',
            ($withoutOperator ? null : '='),
            ($withoutValue ? null : 'value')
        );
    }

    public function testWithUnknownOperator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The arguments which you have supplied cannot be parsed');

        $this->builder->where('field', 'something', 'value');
    }

    public function testWithFilterObject(): void
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

            $this->assertCount(3, $filters['fields']);
        }

        $this->assertEquals($this->builder, $response);
    }

    public function testWhereClosure(): void
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
     * Test the whereBetween
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWhereBetween(): void
    {
        $in = $this->getFilterMock(BetweenFilter::class);
        $in->shouldReceive('__construct')
            ->once()
            ->with('age', 16, 18, null, null);

        $this->builder->shouldReceive('where')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->whereBetween('age', 16, 18);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the orWhereBetween method.
     */
    public function testOrWhereBetween(): void
    {
        $this->builder->shouldReceive('whereBetween')
            ->with('age', 18, 22, null, 'numeric', 'or')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->orWhereBetween('age', 18, 22, null, 'numeric');

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the orWhereNotBetween method.
     */
    public function testOrWhereNotBetween(): void
    {
        $this->builder->where('foobar', '=', 'baz');

        /** @noinspection PhpDeprecationInspection */
        $response = $this->builder->orWhereNotBetween('age', 18, 22, null, 'numeric');

        $filter = $response->getFilter();

        $this->assertInstanceOf(FilterInterface::class, $filter);
        $this->assertEquals([
            'type'   => 'or',
            'fields' => [
                [
                    'type'      => 'selector',
                    'dimension' => 'foobar',
                    'value'     => 'baz',
                ],
                [
                    'type'  => 'not',
                    'field' =>
                        [
                            'type'        => 'bound',
                            'dimension'   => 'age',
                            'ordering'    => 'numeric',
                            'lower'       => '18',
                            'lowerStrict' => false,
                            'upper'       => '22',
                            'upperStrict' => true,
                        ],
                ],
            ],
        ], $filter ? $filter->toArray() : []);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the whereNotBetween
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWhereNotBetween(): void
    {
        $this->getFilterMock(BetweenFilter::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('age', 16, 18, null, null);

        $this->getFilterMock(NotFilter::class)
            ->shouldReceive('__construct')
            ->once()
            ->with(new IsInstanceOf(BetweenFilter::class));

        $this->builder->shouldReceive('where')
            ->once()
            ->andReturn($this->builder);

        /** @noinspection PhpDeprecationInspection */
        $response = $this->builder->whereNotBetween('age', 16, 18);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the whereColumn
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWhereColumn(): void
    {
        $this->getFilterMock(ColumnComparisonFilter::class)
            ->shouldReceive('__construct')
            ->once()
            ->with(
                new IsEqual(new Dimension('dimensionA')),
                new IsEqual(new Dimension('dimensionB'))
            );

        $response = $this->builder->whereColumn('dimensionA', 'dimensionB');

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the orWhereColumn method.
     */
    public function testOrWhereColumn(): void
    {
        $this->builder->shouldReceive('whereColumn')
            ->with('name', 'first_name', 'or')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->orWhereColumn('name', 'first_name');

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the orWhereFlags method.
     */
    public function testOrWhereFlags(): void
    {
        $this->builder->shouldReceive('whereFlags')
            ->with('flags', 32, 'or')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->orWhereFlags('flags', 32);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the orWhereNotColumn method.
     */
    public function testOrWhereNotColumn(): void
    {
        $this->builder->where('type', '=', 'foobar');
        $response = $this->builder->orWhereNotColumn('name', 'first_name');

        $filter = $response->getFilter();
        $this->assertInstanceOf(FilterInterface::class, $filter);

        $this->assertEquals([
            'type'   => 'or',
            'fields' => [
                [
                    'type'      => 'selector',
                    'dimension' => 'type',
                    'value'     => 'foobar',
                ],
                [
                    'type'  => 'not',
                    'field' => [
                        'type'       => 'columnComparison',
                        'dimensions' => [
                            [
                                'type'       => 'default',
                                'dimension'  => 'name',
                                'outputType' => 'string',
                                'outputName' => 'name',
                            ],
                            [
                                'type'       => 'default',
                                'dimension'  => 'first_name',
                                'outputType' => 'string',
                                'outputName' => 'first_name',
                            ],
                        ],
                    ],
                ],
            ],
        ], $filter ? $filter->toArray() : []);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the whereNotColumn
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWhereNotColumn(): void
    {
        $this->getFilterMock(ColumnComparisonFilter::class)
            ->shouldReceive('__construct')
            ->once()
            ->with(
                new IsEqual(new Dimension('dimensionA')),
                new IsEqual(new Dimension('dimensionB'))
            );

        $this->getFilterMock(NotFilter::class)
            ->shouldReceive('__construct')
            ->once()
            ->with(new IsInstanceOf(ColumnComparisonFilter::class));

        /** @noinspection PhpDeprecationInspection */
        $response = $this->builder->whereNotColumn('dimensionA', 'dimensionB');

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the whereFlags filter
     * @testWith ["0.18.0"]
     *           ["0.21.1"]
     *           [null]
     */
    public function testWhereFlags(string $version = null): void
    {
        $extractionBuilder = Mockery::mock(ExtractionBuilder::class);

        $this->client = new DruidClient(['version' => $version]);

        $this->builder = Mockery::mock(QueryBuilder::class, [$this->client, 'http://']);
        $this->builder->makePartial();

        if (!empty($version) && version_compare($version, '0.20.2', '>=')) {
            $this->builder->shouldReceive('virtualColumn')
                ->once()
                ->with('bitwiseAnd("flags", 33)', 'v0', DataType::LONG)
                ->andReturn($this->builder);

            $this->builder->shouldReceive('where')
                ->once()
                ->with('v0', '=', 33, null, 'and')
                ->andReturn($this->builder);
        } else {
            $this->builder->shouldReceive('where')
                ->once()
                ->withArgs(function ($dimension, $operator, $flags, $extractionClosure, $boolean) use (
                    $extractionBuilder
                ) {
                    $this->assertEquals('flags', $dimension);
                    $this->assertEquals('=', $operator);
                    $this->assertEquals(33, $flags);
                    $this->assertInstanceOf(Closure::class, $extractionClosure);
                    $this->assertEquals('and', $boolean);

                    $extractionBuilder->shouldReceive('javascript')
                        ->once()
                        ->withArgs(function ($js) {
                            $this->assertStringStartsWith('function(dimensionValue) {', trim($js));

                            return true;
                        });

                    $extractionClosure($extractionBuilder);

                    return true;
                })
                ->andReturn($this->builder);
        }

        $response = $this->builder->whereFlags('flags', 33);

        $this->assertEquals($this->builder, $response);
    }

    public function testFlagsInFilterBuilder()
    {
        $this->client = new DruidClient(['version' => '0.20.2']);

        $this->builder = Mockery::mock(QueryBuilder::class, [$this->client, 'http://']);
        $this->builder->makePartial();

        $response = $this->builder->where(function (FilterBuilder $filterBuilder) {
            $filterBuilder->whereFlags('flags', 32);
        });

        $filter = $this->builder->getFilter();

        $this->assertInstanceOf(FilterInterface::class, $filter);

        $this->assertEquals([
            'type'      => 'selector',
            'dimension' => 'v0',
            'value'     => 32,
        ], $filter ? $filter->toArray() : []);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the whereIn
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWhereIn(): void
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
     * Test the orWhereIn method.
     */
    public function testOrWhereIn(): void
    {
        $this->builder->shouldReceive('whereIn')
            ->with('country_iso', ['nl', 'be'], null, 'or')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->orWhereIn('country_iso', ['nl', 'be']);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the whereNot method.
     */
    public function testWhereNot(): void
    {
        $this->builder->shouldReceive('where')
            ->withArgs(function ($filter, $n1, $n2, $n3, $boolean) {

                $this->assertInstanceOf(NotFilter::class, $filter);
                $this->assertNull($n1);
                $this->assertNull($n2);
                $this->assertNull($n3);
                $this->assertEquals('and', $boolean);

                return true;
            })
            ->once()
            ->andReturn($this->builder);

        $closure = function (FilterBuilder $filterBuilder) {
            $filterBuilder->whereIn('age', [18, 19]);
        };

        $response = $this->builder->whereNot($closure);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the whereNot method without a filter given
     */
    public function testWhereNotWithoutFilter(): void
    {
        $closure = function (FilterBuilder $filterBuilder) {
            // Nothing happens here.
        };

        $response = $this->builder->whereNot($closure);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the orWhereNot method.
     */
    public function testOrWhereNot(): void
    {
        $this->builder->shouldReceive('whereNot')
            ->with(new IsInstanceOf(Closure::class), 'or')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->orWhereNot(function (FilterBuilder $filterBuilder) {
            $filterBuilder->whereIn('age', [18, 19]);
        });

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the orWhereNotIn method.
     */
    public function testOrWhereNotIn(): void
    {
        $this->builder->where('foo', '=', 'bar');

        /** @noinspection PhpDeprecationInspection */
        $response = $this->builder->orWhereNotIn('country_iso', ['nl', 'be']);

        $filter = $response->getFilter();

        $this->assertInstanceOf(FilterInterface::class, $filter);

        $this->assertEquals([
            'type'   => 'or',
            'fields' => [
                [
                    'type'      => 'selector',
                    'dimension' => 'foo',
                    'value'     => 'bar',
                ],
                [
                    'type'  => 'not',
                    'field' => [
                        'type'      => 'in',
                        'dimension' => 'country_iso',
                        'values'    => ['nl', 'be',],
                    ],
                ],
            ],
        ], $filter ? $filter->toArray() : []);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @throws \Exception
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWhereInterval(): void
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
     * Test the orWhereInterval method.
     */
    public function testOrWhereInterval(): void
    {
        $interval = new Interval('now', 'tomorrow');

        $this->builder->shouldReceive('whereInterval')
            ->with('__time', [$interval->getStart(), $interval->getStop()], null, 'or')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->orWhereInterval('__time', [$interval->getStart(), $interval->getStop()], null);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @throws \Exception
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWhereNotInterval(): void
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

        $this->getFilterMock(NotFilter::class)
            ->shouldReceive('__construct')
            ->once()
            ->with(new IsInstanceOf(IntervalFilter::class));

        /** @noinspection PhpDeprecationInspection */
        $response = $this->builder->whereNotInterval('__time', [$interval->getStart(), $interval->getStop()], null);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the orWhereNotInterval method.
     */
    public function testOrWhereNotInterval(): void
    {
        $interval = new Interval('2021-04-01T06:45:26.000Z/2021-04-02T00:00:00.000Z');

        $this->builder->where('foo', '=', 'bar');

        /** @noinspection PhpDeprecationInspection */
        $response = $this->builder->orWhereNotInterval('__time', [$interval->getStart(), $interval->getStop()], null);

        $filter = $response->getFilter();

        $this->assertInstanceOf(FilterInterface::class, $filter);

        $this->assertEquals([
            'type'   => 'or',
            'fields' => [
                [
                    'type'      => 'selector',
                    'dimension' => 'foo',
                    'value'     => 'bar',
                ],
                [
                    'type'  => 'not',
                    'field' =>
                        [
                            'type'      => 'interval',
                            'dimension' => '__time',
                            'intervals' => [
                                '2021-04-01T06:45:26.000Z/2021-04-02T00:00:00.000Z',
                            ],
                        ],
                ],
            ],
        ], $filter ? $filter->toArray() : []);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the orWhere method.
     */
    public function testOrWhere(): void
    {
        $this->builder->shouldReceive('where')
            ->with('name', '=', 'John', null, 'or')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->orWhere('name', '=', 'John');

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the whereNotIn
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWhereNotIn(): void
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

        /** @noinspection PhpDeprecationInspection */
        $response = $this->builder->whereNotIn('age', [16, 17, 18]);

        $this->assertEquals($this->builder, $response);
    }

    public function testWhereUsingExtraction(): void
    {
        $counter = 0;
        $this->builder->whereIn('user_id', ['bob', 'john'], function (ExtractionBuilder $builder) use (&$counter) {
            $counter++;
            $builder->lookup('username', false);
        });

        $this->assertEquals(1, $counter);
    }

    /**
     * @testWith [true]
     *           [false]
     *
     * Test columnCompareDimension with a closure.
     *
     * @param bool $withMoreThenOne
     */
    public function testColumnCompareDimensionWithClosure(bool $withMoreThenOne): void
    {
        $dimension = new Dimension(
            'name',
            'name',
            'string',
            new SubstringExtraction(2)
        );

        if ($withMoreThenOne) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('Your dimension builder should select 1 dimension');
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->builder
            ->shouldAllowMockingProtectedMethods()
            ->columnCompareDimension(function (DimensionBuilder $dimensionBuilder) use ($dimension, $withMoreThenOne) {
                $dimensionBuilder->select($dimension);

                if ($withMoreThenOne) {
                    $dimensionBuilder->select('first_name');
                }
            });

        $this->assertEquals($response, $dimension);
    }

    /**
     * Test that a string given to columnCompareDimension() will be converted to a Dimension object.
     */
    public function testColumnCompareDimensionWithString(): void
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $response = $this->builder->shouldAllowMockingProtectedMethods()->columnCompareDimension('hi');

        $this->assertEquals(new Dimension('hi'), $response);
    }

    /**
     * @throws \ReflectionException
     */
    public function testVirtualColumns(): void
    {
        $builder  = new QueryBuilder(new DruidClient([]), 'dataSource');
        $response = $builder->virtualColumn('concat(foo, bar)', 'fooBar');

        $this->assertEquals($builder, $response);

        $this->assertEquals([
            new VirtualColumn('concat(foo, bar)', 'fooBar', 'string'),
        ], $this->getProperty($builder, 'virtualColumns'));
    }
}
