<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\HavingFilters;

use Level23\Druid\Tests\TestCase;
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
