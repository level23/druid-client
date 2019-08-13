<?php
declare(strict_types=1);

namespace tests\Level23\Druid\HavingFilters;

use Level23\Druid\HavingFilters\EqualToHavingFilter;
use tests\TestCase;

class EqualToHavingFilterTest extends TestCase
{
    public function testHavingFilter()
    {
        $filter = new EqualToHavingFilter('age', 16);

        $this->assertEquals([
            'type'        => 'equalTo',
            'aggregation' => 'age',
            'value'       => 16,
        ], $filter->getHavingFilter());
    }
}