<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\HavingFilters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\HavingFilters\DimensionSelectorHavingFilter;

class DimensionSelectorHavingFilterTest extends TestCase
{
    public function testHavingFilter(): void
    {
        $filter = new DimensionSelectorHavingFilter('name', 'John');

        $this->assertEquals([
            'type'      => 'dimSelector',
            'dimension' => 'name',
            'value'     => 'John',
        ], $filter->toArray());
    }
}
