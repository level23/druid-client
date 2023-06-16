<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Limits;

use Level23\Druid\Limits\Limit;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\OrderBy\OrderBy;
use Level23\Druid\Types\SortingOrder;
use Level23\Druid\Types\OrderByDirection;
use Level23\Druid\Collections\OrderByCollection;

class LimitTest extends TestCase
{
    public function testLimit(): void
    {
        $limit = new Limit(2715);

        $this->assertEquals([
            'type'    => 'default',
            'limit'   => 2715,
            'columns' => [],
        ], $limit->toArray());

        $this->assertEquals(2715, $limit->getLimit());

        $limit->setLimit(9372);
        $this->assertEquals(9372, $limit->getLimit());

        $limit->setOffset(20);

        $obj = new OrderBy(
            'name',
            OrderByDirection::DESC,
            SortingOrder::NUMERIC
        );

        $limit->addOrderBy($obj);

        $this->assertEquals([
            'type'    => 'default',
            'limit'   => 9372,
            'offset'  => 20,
            'columns' => [
                [
                    'dimension'      => 'name',
                    'direction'      => OrderByDirection::DESC->value,
                    'dimensionOrder' => SortingOrder::NUMERIC->value,
                ],
            ],
        ], $limit->toArray());

        $collection = $limit->getOrderByCollection();
        $this->assertInstanceOf(OrderByCollection::class, $collection);
        $this->assertCount(1, $collection);

        $this->assertEquals($obj, $collection[0]);
    }

    public function testLimitWithoutCollection(): void
    {
        $limit = new Limit(2829);

        $this->assertInstanceOf(OrderByCollection::class, $limit->getOrderByCollection());
        $this->assertCount(0, $limit->getOrderByCollection());
    }

    public function testLimitWithCollection(): void
    {
        $collection = new OrderByCollection();
        $collection->add(new OrderBy('name'));

        $limit = new Limit(2829, $collection);

        $this->assertInstanceOf(OrderByCollection::class, $limit->getOrderByCollection());
        $this->assertEquals($collection, $limit->getOrderByCollection());
    }
}
