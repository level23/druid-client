<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use Level23\Druid\Filters\AndFilter;
use Level23\Druid\Filters\SelectorFilter;
use tests\TestCase;

class AndFilterTest extends TestCase
{
    public function testFilter()
    {
        $filter1 = new SelectorFilter('name', 'Piet');
        $filter2 = new SelectorFilter('age', '11');
        $filter3 = new SelectorFilter('gender', 'strange');

        $filter = new AndFilter([$filter1, $filter2, $filter3]);

        $this->assertEquals([
            'type'   => 'and',
            'fields' => [$filter1->getFilter(), $filter2->getFilter(), $filter3->getFilter()],
        ], $filter->getFilter());
    }
}