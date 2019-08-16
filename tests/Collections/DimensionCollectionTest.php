<?php

declare(strict_types=1);

namespace tests\Level23\Druid\Collections;

use Level23\Druid\Collections\DimensionCollection;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Dimensions\DimensionInterface;
use Mockery;
use tests\TestCase;

class DimensionCollectionTest extends TestCase
{
    public function testGetType()
    {
        $collection = new DimensionCollection();
        $this->assertEquals(DimensionInterface::class, $collection->getType());
    }

    public function testToArray()
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
