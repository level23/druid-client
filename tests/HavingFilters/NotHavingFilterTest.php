<?php
declare(strict_types=1);

namespace tests\Level23\Druid\HavingFilters;

use tests\TestCase;
use Level23\Druid\HavingFilters\NotHavingFilter;
use Level23\Druid\HavingFilters\LessThanHavingFilter;

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
