<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\HavingFilters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\HavingFilters\NotHavingFilter;
use Level23\Druid\HavingFilters\LessThanHavingFilter;

class NotHavingFilterTest extends TestCase
{
    public function testHavingFilter(): void
    {
        $lessFilter = new LessThanHavingFilter('age', 16);

        $filter = new NotHavingFilter($lessFilter);

        $this->assertEquals([
            'type'       => 'not',
            'havingSpec' => $lessFilter->toArray(),
        ], $filter->toArray());
    }
}
