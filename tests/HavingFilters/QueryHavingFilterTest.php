<?php
declare(strict_types=1);

namespace tests\Level23\Druid\HavingFilters;

use Level23\Druid\Filters\InFilter;
use Level23\Druid\HavingFilters\QueryHavingFilter;
use tests\TestCase;

class QueryHavingFilterTest extends TestCase
{
    public function testHavingFilter()
    {
        $inFilter = new InFilter('age', [16, 17, 18]);
        $filter   = new QueryHavingFilter($inFilter);

        $this->assertEquals([
            'type'   => 'filter',
            'filter' => $inFilter->toArray(),
        ], $filter->toArray());
    }
}