<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\SpatialRectangularFilter;

class SpatialRectangularFilterTest extends TestCase
{
    public function testFilter(): void
    {
        $filter = new SpatialRectangularFilter('field', [1, 2], [3, 4]);

        $this->assertEquals([
            'type'      => 'spatial',
            'dimension' => 'field',
            'bound'     => [
                'type'      => 'rectangular',
                'minCoords' => [1, 2],
                'maxCoords' => [3, 4],
            ],
        ], $filter->toArray());
    }
}
