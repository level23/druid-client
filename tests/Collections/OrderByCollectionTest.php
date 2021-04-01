<?php

declare(strict_types=1);

namespace Level23\Druid\Tests\Collections;

use Mockery;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\OrderBy\OrderBy;
use Level23\Druid\OrderBy\OrderByInterface;
use Level23\Druid\Collections\OrderByCollection;

class OrderByCollectionTest extends TestCase
{
    public function testGetType(): void
    {
        $collection = new OrderByCollection();
        $this->assertEquals(OrderByInterface::class, $collection->getType());
    }

    public function testToArray(): void
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
