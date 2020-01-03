<?php
declare(strict_types=1);

namespace tests\Level23\Druid\HavingFilters;

use tests\TestCase;
use Level23\Druid\HavingFilters\DimensionSelectorHavingFilter;

class DimensionSelectorHavingFilterTest extends TestCase
{
    public function testHavingFilter()
    {
        $filter = new DimensionSelectorHavingFilter('name', 'John');

        $this->assertEquals([
            'type'      => 'dimSelector',
            'dimension' => 'name',
            'value'     => 'John',
        ], $filter->toArray());
    }
}
