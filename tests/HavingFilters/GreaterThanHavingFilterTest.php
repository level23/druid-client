<?php
declare(strict_types=1);

namespace tests\Level23\Druid\HavingFilters;

use Level23\Druid\HavingFilters\EqualToHavingFilter;
use Level23\Druid\HavingFilters\GreaterThanHavingFilter;
use tests\TestCase;

class GreaterThanHavingFilterTest extends TestCase
{
    public function testHavingFilter()
    {
        $filter = new GreaterThanHavingFilter('age', 16);

        $this->assertEquals([
            'type'        => 'greaterThan',
            'aggregation' => 'age',
            'value'       => 16,
        ], $filter->toArray());
    }
}