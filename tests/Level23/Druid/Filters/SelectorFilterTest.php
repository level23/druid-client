<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use Level23\Druid\Filters\SelectorFilter;
use tests\TestCase;

class SelectorFilterTest extends TestCase
{
    public function testFilter()
    {
        $filter = new SelectorFilter('name', 'Piet');

        $this->assertEquals([
            'type'      => 'selector',
            'dimension' => 'name',
            'value'     => 'Piet',
        ], $filter->getFilter());
    }
}