<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\SpatialRadiusFilter;

class SpatialRadiusFilterTest extends TestCase
{
    public function testFilter(): void
    {
        $filter = new SpatialRadiusFilter('field', [1, 2], 1.2);

        $this->assertEquals([
            'type'      => 'spatial',
            'dimension' => 'field',
            'bound'     => [
                'type'   => 'radius',
                'coords' => [1, 2],
                'radius' => 1.2,
            ],
        ], $filter->toArray());
    }
}
