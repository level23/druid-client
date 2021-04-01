<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\HavingFilters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\InFilter;
use Level23\Druid\HavingFilters\QueryHavingFilter;

class QueryHavingFilterTest extends TestCase
{
    public function testHavingFilter(): void
    {
        $inFilter = new InFilter('age', [16, 17, 18]);
        $filter   = new QueryHavingFilter($inFilter);

        $this->assertEquals([
            'type'   => 'filter',
            'filter' => $inFilter->toArray(),
        ], $filter->toArray());
    }
}
