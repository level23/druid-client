<?php
declare(strict_types=1);

namespace tests\Level23\Druid\HavingFilters;

use tests\TestCase;
use Level23\Druid\HavingFilters\EqualToHavingFilter;

class EqualToHavingFilterTest extends TestCase
{
    public function testHavingFilter()
    {
        $filter = new EqualToHavingFilter('age', 16);

        $this->assertEquals([
            'type'        => 'equalTo',
            'aggregation' => 'age',
            'value'       => 16,
        ], $filter->toArray());
    }
}