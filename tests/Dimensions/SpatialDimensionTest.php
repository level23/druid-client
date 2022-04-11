<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Dimensions;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Dimensions\SpatialDimension;

class SpatialDimensionTest extends TestCase
{
    public function testDimension(): void
    {

        $dimension = new SpatialDimension('location', ['lat', 'long']);

        $this->assertEquals(
            [
                'dimName' => 'location',
                'dims'    => ['lat', 'long'],
            ],
            $dimension->toArray()
        );
    }
}
