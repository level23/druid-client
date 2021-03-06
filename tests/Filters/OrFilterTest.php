<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\OrFilter;
use Level23\Druid\Filters\SelectorFilter;

class OrFilterTest extends TestCase
{
    public function testFilter(): void
    {
        $filter1 = new SelectorFilter('name', 'John');
        $filter2 = new SelectorFilter('age', '11');
        $filter3 = new SelectorFilter('gender', 'strange');

        $filter = new OrFilter([$filter1, $filter2, $filter3]);

        $this->assertEquals([
            'type'   => 'or',
            'fields' => [$filter1->toArray(), $filter2->toArray(), $filter3->toArray()],
        ], $filter->toArray());
    }
}
