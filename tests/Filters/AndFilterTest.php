<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use tests\TestCase;
use Level23\Druid\Filters\AndFilter;
use Level23\Druid\Filters\SelectorFilter;

class AndFilterTest extends TestCase
{
    public function testFilter()
    {
        $filter1 = new SelectorFilter('name', 'John');
        $filter2 = new SelectorFilter('age', '11');
        $filter3 = new SelectorFilter('gender', 'strange');

        $filter = new AndFilter([$filter1, $filter2, $filter3]);

        $this->assertEquals([
            'type'   => 'and',
            'fields' => [$filter1->toArray(), $filter2->toArray(), $filter3->toArray()],
        ], $filter->toArray());

        $filter4 = new SelectorFilter('car', 'bmw');

        $filter->addFilter($filter4);

        $this->assertEquals([
            'type'   => 'and',
            'fields' => [$filter1->toArray(), $filter2->toArray(), $filter3->toArray(), $filter4->toArray()],
        ], $filter->toArray());
    }
}
