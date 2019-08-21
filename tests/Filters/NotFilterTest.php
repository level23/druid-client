<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use Level23\Druid\Filters\NotFilter;
use Level23\Druid\Filters\SelectorFilter;
use tests\TestCase;

class NotFilterTest extends TestCase
{
    public function testFilter()
    {
        $filterName = new SelectorFilter('name', 'John');

        $filter = new NotFilter($filterName);

        $this->assertEquals([
            'type'  => 'not',
            'field' => $filterName->toArray(),
        ], $filter->toArray());
    }
}