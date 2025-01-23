<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Concerns;

use Mockery;
use Closure;
use DateTime;
use Exception;
use TypeError;
use Hamcrest\Core\IsEqual;
use Mockery\MockInterface;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Hamcrest\Core\IsInstanceOf;
use Mockery\LegacyMockInterface;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\DataType;
use Level23\Druid\Filters\InFilter;
use Level23\Druid\Filters\OrFilter;
use Level23\Druid\Filters\AndFilter;
use Level23\Druid\Filters\NotFilter;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Filters\LikeFilter;
use Level23\Druid\Metadata\Structure;
use Level23\Druid\Filters\NullFilter;
use Level23\Druid\Filters\RegexFilter;
use Level23\Druid\Filters\RangeFilter;
use Level23\Druid\Filters\SearchFilter;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Filters\FilterBuilder;
use Level23\Druid\Filters\BetweenFilter;
use Level23\Druid\Filters\IntervalFilter;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\Filters\EqualityFilter;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Filters\JavascriptFilter;
use Level23\Druid\Metadata\MetadataBuilder;
use Level23\Druid\Filters\ExpressionFilter;
use Level23\Druid\Dimensions\DimensionBuilder;
use Level23\Druid\Filters\SpatialRadiusFilter;
use Level23\Druid\Filters\ArrayContainsFilter;
use Level23\Druid\VirtualColumns\VirtualColumn;
use Level23\Druid\Filters\SpatialPolygonFilter;
use Level23\Druid\Filters\ColumnComparisonFilter;
use Level23\Druid\Filters\SpatialRectangularFilter;
use Level23\Druid\Filters\LogicalExpressionFilterInterface;

class HasFilterTest extends TestCase
{
    /**
     * @var \Level23\Druid\DruidClient
     */
    protected DruidClient $client;

    protected QueryBuilder|MockInterface|LegacyMockInterface $builder;

    public function setUp(): void
    {
        $this->client  = new DruidClient([]);
        $this->builder = Mockery::mock(QueryBuilder::class, [$this->client, 'https://']);
        $this->builder->makePartial();
    }

    /**
     * @return array<array<string|int|float|bool|null|string[]>>
     */
    public static function whereDataProvider(): array
    {
        return [
            ['name', '=', 'John', 'and'],
            ['name', '=', 1, 'and'],
            ['name', '=', null, 'and'],
            ['name', null, null, 'and'],
            ['name', '!=', null, 'and'],
            ['age', '>', null, 'and'],
            ['name', 'John', null, 'and'],
            ['id', '0', null, 'and'],
            ['name', '!=', 'John', 'and'],
            ['name', '!=', 1, 'and'],
            ['name', '<>', 'John', 'AND'],
            ['age', '>', '18', 'and'],
            ['age', '>=', 18, 'and'],
            ['age', '>=', 18.5, 'and'],
            ['age', '=', true, 'and'],
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

    /**
     * @return  array<int,array<int,array<int,mixed>|string>>
     */
    public static function normalizeIntervalsDataProvider(): array
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
                [],
                [],
            ],
            [
                [null],
                [],
                InvalidArgumentException::class,
            ],
            [
                ['/'],
                [],
                InvalidArgumentException::class,
            ],
            [
                ['', '', ''],
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
     * @param array<null|int|string|DateTime|array<string>> $given
     * @param array<Interval>                               $expected
     * @param string                                        $expectException
     *
     * @throws \Exception
     * @dataProvider normalizeIntervalsDataProvider
     */
    public function testNormalizeIntervals(array $given, array $expected, string $expectException = ""): void
    {
        if (!empty($expectException)) {
            $this->expectException($expectException);

            $this->expectExceptionMessage('Invalid type given in the interval array. We cannot process ');
        }
        $response = $this->builder->shouldAllowMockingProtectedMethods()->normalizeIntervals($given);

        $this->assertEquals($expected, $response);
    }

    /**
     * @param string $class
     *
     * @return LegacyMockInterface|MockInterface
     */
    protected function getFilterMock(string $class): LegacyMockInterface|MockInterface
    {
        return $this->getConstructorMock($class, FilterInterface::class);
    }

    /**
     * @dataProvider        whereDataProvider
     *
     * @param string                              $field
     * @param string|null                         $operator
     * @param float|bool|int|string|null|string[] $value
     * @param string                              $boolean
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws \Exception
     */
    public function testWhere(
        string $field,
        string|null $operator,
        float|bool|int|string|null|array $value,
        string $boolean
    ): void {
        if ($operator === null || $value === null) {
            $operator = '=';
        }

        if ($value === null && !in_array($operator, ['=', '!=', '<>'])) {
            $testingValue    = $operator;
            $testingOperator = '=';
        } else {
            $testingOperator = strtolower($operator);
            $testingValue    = $value;
        }

        $not = false;
        if ($operator == '!=' || $operator == '<>' || str_starts_with($testingOperator, 'not')) {

            if (str_starts_with($testingOperator, 'not')) {
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
                $class = RangeFilter::class;
                $this->getFilterMock(RangeFilter::class)
                    ->shouldReceive('__construct')
                    ->with($field, $testingOperator, $testingValue, null)
                    ->once();
                break;

            case 'like':
                $class = LikeFilter::class;
                $this->getFilterMock(LikeFilter::class)
                    ->shouldReceive('__construct')
                    ->with($field, is_scalar($testingValue) ? (string)$testingValue : null, '\\')
                    ->once();
                break;

            case 'search':
                $class = SearchFilter::class;
                $this->getFilterMock(SearchFilter::class)
                    ->shouldReceive('__construct')
                    ->with($field, $testingValue, false)
                    ->once();
                break;

            case     '!=':
            case     '=':
                $class = EqualityFilter::class;
                $this->getFilterMock(EqualityFilter::class)
                    ->shouldReceive('__construct')
                    ->with($field, $testingValue, null)
                    ->once();
                break;

            default:
                $types = [
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
                    ->with($field, $testingValue)
                    ->once();
                break;
        }

        $response = $this->builder->where($field, $operator, $value, $boolean);
        $this->assertEquals($this->builder, $response);

        if ($not) {
            $this->assertInstanceOf(NotFilter::class, $this->builder->getFilter());
        } else {
            $this->assertInstanceOf($class, $this->builder->getFilter());
        }

        // add another
        $this->builder->where($field, $operator, $value, $boolean);

        if (strtolower($boolean) == 'and') {
            $this->assertInstanceOf(AndFilter::class, $this->builder->getFilter());
        } else {
            $this->assertInstanceOf(OrFilter::class, $this->builder->getFilter());
        }
    }

    /**
     * @testWith ["search"]
     *           ["SeArCh"]
     *           ["="]
     *           ["!="]
     *           ["not search"]
     *           ["not SeArCH"]
     *
     * @param string $operator
     *
     * @return void
     */
    public function testWhereWithArrayValue(string $operator): void
    {
        $operator = strtolower($operator);
        if (!in_array($operator, ['search', 'not search'])) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('Given $value is invalid in combination with operator ' . $operator);
        }

        $result = $this->builder->where('field', $operator, ['value1', 'value2']);

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWhereArrayContains(): void
    {
        $search = $this->getFilterMock(ArrayContainsFilter::class);
        $search->shouldReceive('__construct')
            ->once()
            ->with('foo', 'bar');

        $result = $this->builder->whereArrayContains('foo', 'bar');

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testOrWhereArrayContains(): void
    {
        $this->builder->shouldReceive('whereArrayContains')
            ->once()
            ->with('foo', 'bar', 'or')
            ->andReturnSelf();

        $result = $this->builder->orWhereArrayContains('foo', 'bar');

        $this->assertEquals($this->builder, $result);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWhereNull(): void
    {
        $search = $this->getFilterMock(NullFilter::class);
        $search->shouldReceive('__construct')
            ->once()
            ->with('city');

        $result = $this->builder->whereNull('city');

        $this->assertEquals($this->builder, $result);
    }

    public function testOrWhereNull(): void
    {
        $this->builder->shouldReceive('whereNull')
            ->with('country', 'or')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->orWhereNull('country');

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @testWith ["search"]
     *           ["not search"]
     *
     * @param string $operator
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWhereSearchWithInt(string $operator): void
    {
        $search = $this->getFilterMock(SearchFilter::class);
        $search->shouldReceive('__construct')
            ->once()
            ->with('field', '12', null);

        $result = $this->builder->where('field', $operator, 12);

        $this->assertEquals($this->builder, $result);
    }

    public function testWhereMultipleAnd(): void
    {
        $this->builder->where('name', '!=', 'John', 'AnD');
        $this->builder->where('name', '!=', 'Doe', 'AnD');
        $this->builder->where('name', '!=', 'Jane', 'AnD');

        $filter = $this->builder->getFilter();
        if ($filter instanceof LogicalExpressionFilterInterface) {
            $this->assertEquals(AndFilter::class, get_class($filter));
            /** @var array<string,array<scalar>> $records */
            $records = $filter->toArray();
            $this->assertCount(3, $records['fields']);
        }
    }

    public function testWhereMultipleOr(): void
    {
        $this->builder->where('name', '!=', 'John', 'Or');
        $this->builder->where('name', '!=', 'Doe', 'OR');
        $this->builder->where('name', '!=', 'Jane', 'OR');

        $filter = $this->builder->getFilter();
        if ($filter instanceof LogicalExpressionFilterInterface) {
            $this->assertEquals(OrFilter::class, get_class($filter));
            /** @var array<string,array<scalar>> $records */
            $records = $filter->toArray();
            $this->assertCount(3, $records['fields']);
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
        $this->expectException(TypeError::class);

        $this->builder->where($field, $operator, $value);
    }

    public function testWhereWithoutOperatorOrValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You have to supply an operator when you supply a dimension as string');
        $this->builder->where(
            'field',
            null,
            'value'
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

        /** @var array<string,array<scalar>> $filters */
        $filters = $filter->toArray();

        $this->assertCount(3, $filters['fields']);

        $this->assertEquals($this->builder, $response);
    }

    public function testWhereClosure(): void
    {
        $where = new EqualityFilter('var', 12.7);

        $counter  = 0;
        $response = $this->builder->where(function (FilterBuilder $builder) use (&$counter, $where) {
            $counter++;
            $builder->where($where);
        });

        $this->assertEquals($this->builder->getFilter(), $where);
        $this->assertEquals(1, $counter);

        $this->assertEquals($this->builder, $response);
    }

    public function testWhereEmptyClosure(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The arguments which you have supplied cannot be parsed.');
        $this->builder->where(function (FilterBuilder $builder) {
        });
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
            ->with('age', 16, 18, null);

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
            ->with('age', 18, 22, DataType::LONG, 'or')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->orWhereBetween('age', 18, 22, DataType::LONG);

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
     * Test the whereExpression method.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWhereExpression(): void
    {
        $this->client = new DruidClient([]);

        $this->builder = Mockery::mock(QueryBuilder::class, [$this->client, 'https://']);
        $this->builder->makePartial();
        $this->builder->shouldAllowMockingProtectedMethods();

        $this->getFilterMock(ExpressionFilter::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('(field == 1)');

        $this->builder
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('addAndFilter')
            ->once();

        $response = $this->builder->whereExpression('(field == 1)', 'AnD');

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the orWhereExpression method.
     */
    public function testOrWhereExpression(): void
    {
        $this->builder->shouldReceive('whereExpression')
            ->with('(field == 1)', 'or')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->orWhereExpression('(field == 1)');

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

    public function testWhereFlags(): void
    {
        $this->client = new DruidClient([]);

        $this->builder = Mockery::mock(QueryBuilder::class, [$this->client, 'https://']);
        $this->builder->makePartial();

        $this->builder->shouldReceive('virtualColumn')
            ->once()
            ->with('bitwiseAnd("flags", 33)', 'v0', DataType::LONG)
            ->andReturn($this->builder);

        $this->builder->shouldReceive('where')
            ->once()
            ->with('v0', '=', 33, 'and')
            ->andReturn($this->builder);

        $response = $this->builder->whereFlags('flags', 33);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @throws \ReflectionException
     */
    public function testFlagsInFilterBuilder(): void
    {
        $this->client = new DruidClient(['version' => '0.20.2']);

        $this->builder = Mockery::mock(QueryBuilder::class, [$this->client, 'https://']);
        $this->builder->makePartial();

        $response = $this->builder->where(function (FilterBuilder $filterBuilder) {
            $filterBuilder->whereFlags('flags', 32);
        });

        $this->builder->whereFlags('flags2', 64);

        $filter = $this->builder->getFilter();

        $this->assertInstanceOf(FilterInterface::class, $filter);

        $this->assertEquals([
            'type'   => 'and',
            'fields' => [
                [
                    'type'           => 'equals',
                    'column'         => 'v0',
                    'matchValue'     => 32,
                    'matchValueType' => 'long',
                ],
                [
                    'type'           => 'equals',
                    'column'         => 'v1',
                    'matchValue'     => 64,
                    'matchValueType' => 'long',
                ],
            ],
        ], $filter ? $filter->toArray() : []);

        /** @var array<VirtualColumn> $virtualColumns */
        $virtualColumns = $this->getProperty($this->builder, 'virtualColumns');
        $this->assertIsArray($virtualColumns);
        $this->assertTrue(sizeof($virtualColumns) == 2);

        $this->assertInstanceOf(VirtualColumn::class, $virtualColumns[0]);
        $virtualColumn = $virtualColumns[0];

        $this->assertEquals([
            'type'       => 'expression',
            'name'       => 'v0',
            'expression' => 'bitwiseAnd("flags", 32)',
            'outputType' => 'long',
        ], $virtualColumn->toArray());

        $virtualColumn = $virtualColumns[1];

        $this->assertEquals([
            'type'       => 'expression',
            'name'       => 'v1',
            'expression' => 'bitwiseAnd("flags2", 64)',
            'outputType' => 'long',
        ], $virtualColumn->toArray());

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
            ->with('country_iso', ['nl', 'be']);

        $this->builder->shouldReceive('where')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->whereIn('country_iso', ['nl', 'be']);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWhereFlagsInTaskBuilder()
    {
        $client = Mockery::mock(DruidClient::class);
        $client->makePartial();

        $metaDataBuilder = Mockery::mock(MetadataBuilder::class);
        $metaDataBuilder->shouldReceive('structure')
            ->once()
            ->andReturn(new Structure('something', [], []));

        $this->getFilterMock(ExpressionFilter::class)
            ->shouldReceive('__construct')
            ->once()
            ->with('bitwiseAnd("flags", 128) == 128');

        $client->shouldReceive('metadata')
            ->once()
            ->andReturn($metaDataBuilder);

        $client->reindex('something')
            ->sum('foo', 'bar', DataType::LONG, function (FilterBuilder $builder) {
                $builder->whereFlags('flags', 128);
            });
    }

    /**
     * Test the orWhereIn method.
     */
    public function testOrWhereIn(): void
    {
        $this->builder->shouldReceive('whereIn')
            ->with('country_iso', ['nl', 'be'], 'or')
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
            ->withArgs(function ($filter, $n1, $n2, $boolean) {

                $this->assertInstanceOf(NotFilter::class, $filter);
                $this->assertNull($n1);
                $this->assertNull($n2);
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
            ->andReturnUsing(function ($dimension, $intervals) use ($interval) {
                $this->assertEquals('__time', $dimension);
                $this->assertIsArray($intervals);
                $this->assertEquals($interval, $intervals[0]);
            });

        $response = $this->builder->whereInterval('__time', [$interval->getStart(), $interval->getStop()]);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * Test the orWhereInterval method.
     *
     * @throws \Exception
     */
    public function testOrWhereInterval(): void
    {
        $interval = new Interval('now', 'tomorrow');

        $this->builder->shouldReceive('whereInterval')
            ->with('__time', [$interval->getStart(), $interval->getStop()], 'or')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->orWhereInterval('__time', [$interval->getStart(), $interval->getStop()]);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testWhereSpatialRectangular(): void
    {
        $filter = $this->getFilterMock(SpatialRectangularFilter::class);
        $filter->shouldReceive('__construct')
            ->once()
            ->with('location', [48.0, 51.0], [49.5, 52.5]);

        $this->builder
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('addAndFilter')
            ->once();

        $this->builder->whereSpatialRectangular('location', [48.0, 51.0], [49.5, 52.5]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testWhereSpatialRadius(): void
    {
        $filter = $this->getFilterMock(SpatialRadiusFilter::class);
        $filter->shouldReceive('__construct')
            ->once()
            ->with('location', [48.0, 51.0], 0.5);

        $this->builder
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('addAndFilter')
            ->once();

        $this->builder->whereSpatialRadius('location', [48.0, 51.0], 0.5);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testWhereSpatialPolygon(): void
    {
        $filter = $this->getFilterMock(SpatialPolygonFilter::class);
        $filter->shouldReceive('__construct')
            ->once()
            ->with('location', [1, 2], [3, 4]);

        $this->builder
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('addAndFilter')
            ->once();

        $this->builder->whereSpatialPolygon('location', [1, 2], [3, 4]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testOrWhereSpatialPolygon(): void
    {
        $filter = $this->getFilterMock(SpatialPolygonFilter::class);
        $filter->shouldReceive('__construct')
            ->once()
            ->with('location', [1, 2], [3, 4]);

        $this->builder
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('addOrFilter')
            ->once();

        $this->builder->orWhereSpatialPolygon('location', [1, 2], [3, 4]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testOrWhereSpatialRadius(): void
    {
        $filter = $this->getFilterMock(SpatialRadiusFilter::class);
        $filter->shouldReceive('__construct')
            ->once()
            ->with('location', [48.0, 51.0], 0.5);

        $this->builder
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('addOrFilter')
            ->once();

        $this->builder->orWhereSpatialRadius('location', [48.0, 51.0], 0.5);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testOrWhereSpatialRectangular(): void
    {
        $filter = $this->getFilterMock(SpatialRectangularFilter::class);
        $filter->shouldReceive('__construct')
            ->once()
            ->with('location', [48.0, 51.0], [49.5, 52.5]);

        $this->builder
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('addOrFilter')
            ->once();

        $this->builder->orWhereSpatialRectangular('location', [48.0, 51.0], [49.5, 52.5]);
    }

    /**
     * Test the orWhere method.
     */
    public function testOrWhere(): void
    {
        $this->builder->shouldReceive('where')
            ->with('name', '=', 'John', 'or')
            ->once()
            ->andReturn($this->builder);

        $response = $this->builder->orWhere('name', '=', 'John');

        $this->assertEquals($this->builder, $response);
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
            'string'
        );

        if ($withMoreThenOne) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('Your dimension builder should select 1 dimension');
        }

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
