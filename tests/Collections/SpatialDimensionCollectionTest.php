<?php

declare(strict_types=1);

namespace Level23\Druid\Tests\Collections;

use Mockery;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Dimensions\SpatialDimension;
use Level23\Druid\Collections\SpatialDimensionCollection;

class SpatialDimensionCollectionTest extends TestCase
{
    public function testGetType(): void
    {
        $collection = new SpatialDimensionCollection();
        $this->assertEquals(SpatialDimension::class, $collection->getType());
    }

    public function testToArray(): void
    {
        $response = [
            'type'       => 'default',
            'dimension'  => 'age',
            'outputName' => 'age',
        ];
        $item     = Mockery::mock(SpatialDimension::class, ['position', ['lat', 'long']]);
        $item->shouldReceive('toArray')
            ->once()
            ->andReturn($response);

        $collection = new SpatialDimensionCollection($item);
        $this->assertEquals([$response], $collection->toArray());
    }
}
