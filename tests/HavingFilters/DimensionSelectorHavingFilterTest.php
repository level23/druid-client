<?php
declare(strict_types=1);

namespace tests\Level23\Druid\HavingFilters;

use Level23\Druid\HavingFilters\DimensionSelectorHavingFilter;
use tests\TestCase;

class DimensionSelectorHavingFilterTest extends TestCase
{
    public function testHavingFilter()
    {
        $filter = new DimensionSelectorHavingFilter('name', 'John');

        $this->assertEquals([
            'type'      => 'dimSelector',
            'dimension' => 'name',
            'value'     => 'John',
        ], $filter->getHavingFilter());
    }
}