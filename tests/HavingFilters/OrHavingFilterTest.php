<?php
declare(strict_types=1);

namespace tests\Level23\Druid\HavingFilters;

use Level23\Druid\HavingFilters\EqualToHavingFilter;
use Level23\Druid\HavingFilters\OrHavingFilter;
use tests\TestCase;

class OrHavingFilterTest extends TestCase
{
    public function testHavingFilter()
    {
        $filter1 = new EqualToHavingFilter('age', 16);
        $filter2 = new EqualToHavingFilter('cars', 0);
        $filter3 = new EqualToHavingFilter('horses', 4);

        $filter = new OrHavingFilter($filter1, $filter2);

        $this->assertEquals([$filter1, $filter2], $filter->getHavingFilters());

        $filter->addHavingFilter($filter3);
        $this->assertEquals([$filter1, $filter2, $filter3], $filter->getHavingFilters());

        $this->assertEquals([
            'type'        => 'or',
            'havingSpecs' => [
                $filter1->getHavingFilter(),
                $filter2->getHavingFilter(),
                $filter3->getHavingFilter(),
            ],
        ], $filter->getHavingFilter());
    }
}