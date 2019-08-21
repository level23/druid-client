<?php
declare(strict_types=1);

namespace tests\Level23\Druid\HavingFilters;

use tests\TestCase;
use Level23\Druid\HavingFilters\AndHavingFilter;
use Level23\Druid\HavingFilters\EqualToHavingFilter;

class AndHavingFilterTest extends TestCase
{
    public function testHavingFilter()
    {
        $filter1 = new EqualToHavingFilter('age', 16);
        $filter2 = new EqualToHavingFilter('cars', 0);
        $filter3 = new EqualToHavingFilter('horses', 4);

        $filter = new AndHavingFilter([$filter1, $filter2]);

        $this->assertEquals([$filter1, $filter2], $filter->getHavingFilters());

        $filter->addHavingFilter($filter3);
        $this->assertEquals([$filter1, $filter2, $filter3], $filter->getHavingFilters());

        $this->assertEquals([
            'type'        => 'and',
            'havingSpecs' => [
                $filter1->toArray(),
                $filter2->toArray(),
                $filter3->toArray(),
            ],
        ], $filter->toArray());
    }
}