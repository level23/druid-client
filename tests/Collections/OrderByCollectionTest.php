<?php

declare(strict_types=1);

namespace tests\Level23\Druid\Collections;

use Level23\Druid\Collections\OrderByCollection;
use Level23\Druid\OrderBy\OrderBy;
use Level23\Druid\OrderBy\OrderByInterface;
use Mockery;
use tests\TestCase;

class OrderByCollectionTest extends TestCase
{
    public function testGetType()
    {
        $collection = new OrderByCollection();
        $this->assertEquals(OrderByInterface::class, $collection->getType());
    }

    public function testToArray()
    {
        $response = [
            'dimension'      => 'age',
            'direction'      => 'ascending',
            'dimensionOrder' => 'lexicographic',
        ];
        $item     = Mockery::mock(OrderBy::class, ['age']);
        $item->shouldReceive('getOrderBy')
            ->once()
            ->andReturn($response);

        $collection = new OrderByCollection($item);
        $this->assertEquals([$response], $collection->toArray());
    }
}
