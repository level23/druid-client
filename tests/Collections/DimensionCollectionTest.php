<?php

declare(strict_types=1);

namespace Level23\Druid\Tests\Collections;

use Mockery;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Dimensions\DimensionInterface;
use Level23\Druid\Collections\DimensionCollection;

class DimensionCollectionTest extends TestCase
{
    public function testGetType(): void
    {
        $collection = new DimensionCollection();
        $this->assertEquals(DimensionInterface::class, $collection->getType());
    }

    public function testToArray(): void
    {
        $response = [
            'type'       => 'default',
            'dimension'  => 'age',
            'outputName' => 'age',
        ];
        $item     = Mockery::mock(Dimension::class, ['age']);
        $item->shouldReceive('toArray')
            ->once()
            ->andReturn($response);

        $collection = new DimensionCollection($item);
        $this->assertEquals([$response], $collection->toArray());
    }
}
