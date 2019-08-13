<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use Level23\Druid\Filters\ColumnComparisonFilter;
use tests\TestCase;

class ColumnComparisonFilterTest extends TestCase
{
    public function testFilter()
    {
        $filter = new ColumnComparisonFilter('name', 'first_name');

        $this->assertEquals([
            'type'       => 'columnComparison',
            'dimensions' => [
                'name',
                'first_name',
            ],
        ], $filter->getFilter());
    }
}