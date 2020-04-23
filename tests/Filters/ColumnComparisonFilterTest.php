<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use tests\Level23\Druid\TestCase;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Filters\ColumnComparisonFilter;

class ColumnComparisonFilterTest extends TestCase
{
    public function testFilter()
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
