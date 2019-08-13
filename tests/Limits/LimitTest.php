<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Limits;

use Level23\Druid\Collections\OrderByCollection;
use Level23\Druid\Limits\Limit;
use Level23\Druid\OrderBy\OrderBy;
use Level23\Druid\Types\OrderByDirection;
use Level23\Druid\Types\SortingOrder;
use tests\TestCase;

class LimitTest extends TestCase
{
    public function testLimit()
    {
        $limit = new Limit(2715);

        $this->assertEquals([
            'type'    => 'default',
            'limit'   => 2715,
            'columns' => [],
        ], $limit->getLimitForQuery());

        $this->assertEquals(2715, $limit->getLimit());

        $limit->setLimit(9372);
        $this->assertEquals(9372, $limit->getLimit());

        $obj = new OrderBy(
            'name',
            OrderByDirection::DESC(),
            SortingOrder::NUMERIC()
        );

        $limit->addOrderBy($obj);

        $this->assertEquals([
            'type'    => 'default',
            'limit'   => 9372,
            'columns' => [
                [
                    'dimension'      => 'name',
                    'direction'      => OrderByDirection::DESC()->getValue(),
                    'dimensionOrder' => SortingOrder::NUMERIC()->getValue(),
                ],
            ],
        ], $limit->getLimitForQuery());

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