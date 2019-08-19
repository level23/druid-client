<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Concerns;

use Level23\Druid\Collections\OrderByCollection;
use Level23\Druid\DruidClient;
use Level23\Druid\Limits\Limit;
use Level23\Druid\Limits\LimitInterface;
use Level23\Druid\OrderBy\OrderBy;
use Level23\Druid\QueryBuilder;
use Level23\Druid\Types\OrderByDirection;
use Level23\Druid\Types\SortingOrder;
use Mockery;
use tests\TestCase;

class HasLimitTest extends TestCase
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

    /**
     * @param string $class
     *
     * @return \Mockery\Generator\MockConfigurationBuilder|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected function getLimitMock(string $class)
    {
        $builder = new Mockery\Generator\MockConfigurationBuilder();
        $builder->setInstanceMock(true);
        $builder->setName($class);
        $builder->addTarget(LimitInterface::class);

        return Mockery::mock($builder);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testLimit()
    {
        $this->getLimitMock(Limit::class)
            ->shouldReceive('__construct')
            ->with(15)
            ->once();

        $response = $this->builder->limit(15);

        $this->assertEquals($this->builder, $response);
        $this->assertInstanceOf(Limit::class, $this->builder->getLimit());
    }

    public function testLimitUpdate()
    {
        $response = $this->builder->limit(15);

        $this->assertEquals($this->builder, $response);
        if ($this->builder->getLimit() instanceof LimitInterface) {
            $this->assertEquals(15, $this->builder->getLimit()->getLimit());
        }

        $response = $this->builder->limit(18);

        $this->assertEquals($this->builder, $response);
        if ($this->builder->getLimit() instanceof LimitInterface) {
            $this->assertEquals(18, $this->builder->getLimit()->getLimit());
        }
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testOrderBy()
    {
        Mockery::mock('overload:' . OrderBy::class)
            ->shouldReceive('__construct')
            ->andReturnUsing(function ($dimension, $direction, $dimensionOrder) {
                $this->assertEquals('name', $dimension);
                $this->assertEquals(OrderByDirection::DESC(), $direction);
                $this->assertEquals(SortingOrder::ALPHANUMERIC(), $dimensionOrder);
            })
            ->once();

        $limitMock = Mockery::mock('overload:' . Limit::class);

        $limitMock->shouldReceive('__construct')
            ->with(QueryBuilder::$DEFAULT_MAX_LIMIT)
            ->once();

        $limitMock->shouldReceive('addOrderBy')
            ->once()
            ->andReturnUsing(function ($orderBy) {
                $this->assertInstanceOf(OrderBy::class, $orderBy);
            });

        $this->builder->orderBy('name', 'desc', SortingOrder::ALPHANUMERIC());
    }

    public function testSetOrderByWithLimit()
    {
        $this->builder->limit(15);
        $this->builder->orderBy('name', 'asc');

        $this->assertEquals(
            new Limit(15, new OrderByCollection(
                new OrderBy('name', OrderByDirection::ASC(), 'lexicographic')
            )),
            $this->builder->getLimit()
        );
    }
}