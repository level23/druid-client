<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Concerns;

use Mockery;
use tests\TestCase;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Limits\Limit;
use Hamcrest\Core\IsInstanceOf;
use Level23\Druid\OrderBy\OrderBy;
use Level23\Druid\Types\SortingOrder;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\Limits\LimitInterface;
use Level23\Druid\Types\OrderByDirection;
use Level23\Druid\Collections\OrderByCollection;

class HasLimitTest extends TestCase
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
        $this->assertEquals(null, $this->builder->getLimit());

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
            ->with('name', 'DeSc', SortingOrder::ALPHANUMERIC)
            ->once();

        $limitMock = Mockery::mock('overload:' . Limit::class);

        $limitMock->shouldReceive('__construct')
            ->with(QueryBuilder::$DEFAULT_MAX_LIMIT)
            ->once();

        $limitMock->shouldReceive('addOrderBy')
            ->with(new IsInstanceOf(OrderBy::class))
            ->once();

        $result = $this->builder->orderBy('name', 'DeSc', SortingOrder::ALPHANUMERIC);

        $this->assertEquals($this->builder, $result);
    }

    public function testIncorrectOrderByDirect()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid order by direction given:');

        $this->builder->orderBy('name', 'd');
    }

    public function testSetOrderByWithLimit()
    {
        $result = $this->builder->limit(15);
        $this->assertEquals($this->builder, $result);

        $result = $this->builder->orderBy('name', 'ASC');
        $this->assertEquals($this->builder, $result);

        $this->assertEquals(
            new Limit(15, new OrderByCollection(
                new OrderBy('name', OrderByDirection::ASC, 'lexicographic')
            )),
            $this->builder->getLimit()
        );
    }
}