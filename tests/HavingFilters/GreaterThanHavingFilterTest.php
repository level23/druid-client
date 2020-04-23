<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\HavingFilters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\HavingFilters\GreaterThanHavingFilter;

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
