<?php
declare(strict_types=1);

namespace tests\Level23\Druid\HavingFilters;

use tests\Level23\Druid\TestCase;
use Level23\Druid\Filters\InFilter;
use Level23\Druid\HavingFilters\QueryHavingFilter;

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
