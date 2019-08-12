<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use Level23\Druid\Filters\OrFilter;
use Level23\Druid\Filters\SelectorFilter;
use tests\TestCase;

class OrFilterTest extends TestCase
{
    public function testFilter()
    {
        $filter1 = new SelectorFilter('name', 'Piet');
        $filter2 = new SelectorFilter('age', '11');
        $filter3 = new SelectorFilter('gender', 'strange');

        $filter = new OrFilter([$filter1, $filter2, $filter3]);

        $this->assertEquals([
            'type'   => 'or',
            'fields' => [$filter1->getFilter(), $filter2->getFilter(), $filter3->getFilter()],
        ], $filter->getFilter());
    }
}