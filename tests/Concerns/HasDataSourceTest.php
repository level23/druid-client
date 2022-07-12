<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Concerns;

use Mockery;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\JoinType;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\DataSources\JoinDataSource;
use Level23\Druid\DataSources\TableDataSource;
use Level23\Druid\DataSources\UnionDataSource;
use Level23\Druid\DataSources\InlineDataSource;

class HasDataSourceTest extends TestCase
{
    /**
     * @var QueryBuilder|\Mockery\MockInterface|\Mockery\LegacyMockInterface $builder
     */
    protected $builder;

    public function setUp(): void
    {
        $client = new DruidClient([]);

        $this->builder = Mockery::mock(QueryBuilder::class, [$client, 'test', 'all']);
        $this->builder->makePartial();
    }

    public function testFrom(): void
    {
        $this->builder->shouldReceive('dataSource')
            ->once()
            ->with('test')
            ->andReturn($this->builder);

        $this->assertEquals($this->builder, $this->builder->from('test'));
    }

    /**
     * @throws \ReflectionException
     */
    public function testDataSource(): void
    {
        $this->assertEquals($this->builder, $this->builder->dataSource('wikipedia'));

        $dataSource = $this->getProperty($this->builder, 'dataSource');

        $this->assertEquals(new TableDataSource('wikipedia'), $dataSource);

        $inlineDs = new InlineDataSource(['name'], [['john'], ['doe']]);
        $this->builder->dataSource($inlineDs);
        $dataSource = $this->getProperty($this->builder, 'dataSource');

        $this->assertEquals($inlineDs, $dataSource);
    }

    /**
     * @throws \ReflectionException
     */
    public function testJoin(): void
    {
        $this->assertEquals(
            $this->builder,
            $this->builder->join('otherSource', 'o', 'o.name = name')
        );

        $dataSource = $this->getProperty($this->builder, 'dataSource');

        $this->assertInstanceOf(JoinDataSource::class, $dataSource);
    }

    public function testJoinWithoutDataSource(): void
    {
        $builder = new QueryBuilder(new DruidClient([]));

        $tableDs = new TableDataSource('foobar');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You first have to define your "from" dataSource before you can join!');
        $builder->join($tableDs, 'baz', 'baz.id = id');
    }

    public function testJoinUsingSomethingWrong(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid dataSource given! This can either be a string (dataSource name),  ' .
            'an object which implements the DataSourceInterface, or a Closure function which allows ' .
            'you to build a sub-query.'
        );

        $this->builder->join(1, 'o', 'o.name = name');
    }

    /**
     * @throws \ReflectionException
     */
    public function testJoinUsingClosure(): void
    {
        $query = null;
        $this->assertEquals(
            $this->builder,
            $this->builder->join(function (QueryBuilder $queryBuilder) use (&$query) {
                $queryBuilder->dataSource('baz')
                    ->where('foo', '=', 'bar')
                    ->interval('01-05-2022', '01-06-2022');

                $query = $queryBuilder->getQuery();
            }, 'o', 'o.name = name')
        );

        /** @var JoinDataSource $dataSource */
        $dataSource = $this->getProperty($this->builder, 'dataSource');

        $this->assertEquals(
            $query->toArray(),
            $dataSource->toArray()['right']['query']
        );

        $this->assertInstanceOf(JoinDataSource::class, $dataSource);
    }

    /**
     * @throws \ReflectionException
     */
    public function testJoinUsingInterface(): void
    {
        $this->assertEquals(
            $this->builder,
            $this->builder->join(new UnionDataSource(['a', 'b']), 'o', 'o.name = name')
        );

        /** @var JoinDataSource $dataSource */
        $dataSource = $this->getProperty($this->builder, 'dataSource');

        $this->assertEquals(
            ['type' => 'union', 'dataSources' => ['a', 'b']],
            $dataSource->toArray()['right']
        );

        $this->assertInstanceOf(JoinDataSource::class, $dataSource);
    }

    public function testLeftJoin(): void
    {
        $tableDs = new TableDataSource('foobar');
        $this->builder->shouldReceive('join')
            ->once()
            ->with($tableDs, 'baz', 'a = b', JoinType::LEFT)
            ->andReturnSelf();

        $this->assertEquals(
            $this->builder,
            $this->builder->leftJoin($tableDs, 'baz', 'a = b')
        );
    }

    public function testInnerJoin(): void
    {
        $tableDs = new TableDataSource('foobar');
        $this->builder->shouldReceive('join')
            ->once()
            ->with($tableDs, 'baz', 'a = b', JoinType::INNER)
            ->andReturnSelf();

        $this->assertEquals(
            $this->builder,
            $this->builder->innerJoin($tableDs, 'baz', 'a = b')
        );
    }

    public function testUnionWithIncorrectDataSource(): void
    {
        $this->builder->dataSource(new InlineDataSource([], []));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('We can only union an table dataSource! You currently are using a Level23\Druid\DataSources\InlineDataSource');

        $this->builder->union(['a', 'b']);
    }

    public function testUnion(): void
    {
        $this->assertEquals(
            $this->builder,
            $this->builder->union('b')
        );

        $dataSource = $this->getProperty($this->builder, 'dataSource');

        $this->assertInstanceOf(UnionDataSource::class, $dataSource);
    }
}