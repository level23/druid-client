<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\SpatialPolygonFilter;

class SpatialPolygonFilterTest extends TestCase
{
    public function testFilter(): void
    {
        $filter = new SpatialPolygonFilter('field', [1, 2], [3, 4]);

        $this->assertEquals([
            'type'      => 'spatial',
            'dimension' => 'field',
            'bound'     => [
                'type'     => 'polygon',
                'abscissa' => [1, 2],
                'ordinate' => [3, 4],
            ],
        ], $filter->toArray());
    }
}
