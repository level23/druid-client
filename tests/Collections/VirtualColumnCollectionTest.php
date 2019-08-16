<?php

declare(strict_types=1);

namespace tests\Level23\Druid\Collections;

use Level23\Druid\Collections\VirtualColumnCollection;
use Level23\Druid\VirtualColumns\VirtualColumn;
use Level23\Druid\VirtualColumns\VirtualColumnInterface;
use Mockery;
use tests\TestCase;

class VirtualColumnCollectionTest extends TestCase
{
    public function testGetType()
    {
        $collection = new VirtualColumnCollection();
        $this->assertEquals(VirtualColumnInterface::class, $collection->getType());
    }

    public function testToArray()
    {
        $response = [
            'type'       => 'expression',
            'name'       => 'country_iso',
            'expression' => 'if(mccmnc > 0, country_iso, "")',
            'outputType' => 'string',
        ];
        $item     = Mockery::mock(VirtualColumn::class, ['country_iso', 'if(mccmnc > 0, country_iso, "")']);
        $item->shouldReceive('toArray')
            ->once()
            ->andReturn($response);

        $collection = new VirtualColumnCollection($item);
        $this->assertEquals([$response], $collection->toArray());
    }
}
