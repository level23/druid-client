<?php

declare(strict_types=1);

namespace tests\Level23\Druid\Collections;

use Mockery;
use tests\TestCase;
use Level23\Druid\OrderBy\OrderBy;
use Level23\Druid\OrderBy\OrderByInterface;
use Level23\Druid\Collections\OrderByCollection;

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
        $item->shouldReceive('toArray')
            ->once()
            ->andReturn($response);

        $collection = new OrderByCollection($item);
        $this->assertEquals([$response], $collection->toArray());
    }
}
