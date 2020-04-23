<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\HavingFilters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\HavingFilters\LessThanHavingFilter;

class LessThanHavingFilterTest extends TestCase
{
    public function testHavingFilter()
    {
        $filter = new LessThanHavingFilter('age', 16);

        $this->assertEquals([
            'type'        => 'lessThan',
            'aggregation' => 'age',
            'value'       => 16,
        ], $filter->toArray());
    }
}
