<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Limits;

use tests\TestCase;
use Level23\Druid\Limits\Limit;
use Level23\Druid\OrderBy\OrderBy;
use Level23\Druid\Types\SortingOrder;
use Level23\Druid\Types\OrderByDirection;
use Level23\Druid\Collections\OrderByCollection;

class LimitTest extends TestCase
{
    public function testLimit()
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

        $obj = new OrderBy(
            'name',
            OrderByDirection::DESC,
            SortingOrder::NUMERIC
        );

        $limit->addOrderBy($obj);

        $this->assertEquals([
            'type'    => 'default',
            'limit'   => 9372,
            'columns' => [
                [
                    'dimension'      => 'name',
                    'direction'      => OrderByDirection::DESC,
                    'dimensionOrder' => SortingOrder::NUMERIC,
                ],
            ],
        ], $limit->toArray());

        $collection = $limit->getOrderByCollection();
        $this->assertInstanceOf(OrderByCollection::class, $collection);
        $this->assertEquals(1, count($collection));

        $this->assertEquals($obj, $collection[0]);
    }

    public function testLimitWithoutCollection()
    {
        $limit = new Limit(2829);

        $this->assertInstanceOf(OrderByCollection::class, $limit->getOrderByCollection());
        $this->assertEquals(0, count($limit->getOrderByCollection()));
    }

    public function testLimitWithCollection()
    {

        $collection = new OrderByCollection();
        $collection->add(new OrderBy('name'));

        $limit = new Limit(2829, $collection);

        $this->assertInstanceOf(OrderByCollection::class, $limit->getOrderByCollection());
        $this->assertEquals($collection, $limit->getOrderByCollection());
    }
}