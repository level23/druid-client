<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Filters\ColumnComparisonFilter;

class ColumnComparisonFilterTest extends TestCase
{
    public function testFilter(): void
    {
        $dimensionA = new Dimension('name');
        $dimensionB = new Dimension('first_name');

        $filter = new ColumnComparisonFilter($dimensionA, $dimensionB);

        $this->assertEquals([
            'type'       => 'columnComparison',
            'dimensions' => [
                $dimensionA->toArray(),
                $dimensionB->toArray(),
            ],
        ], $filter->toArray());
    }
}
