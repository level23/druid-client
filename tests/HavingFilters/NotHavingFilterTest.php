<?php
declare(strict_types=1);

namespace tests\Level23\Druid\HavingFilters;

use Level23\Druid\HavingFilters\LessThanHavingFilter;
use Level23\Druid\HavingFilters\NotHavingFilter;
use tests\TestCase;

class NotHavingFilterTest extends TestCase
{
    public function testHavingFilter()
    {
        $lessFilter = new LessThanHavingFilter('age', 16);

        $filter = new NotHavingFilter($lessFilter);

        $this->assertEquals([
            'type'       => 'not',
            'havingSpec' => $lessFilter->toArray(),
        ], $filter->toArray());
    }
}