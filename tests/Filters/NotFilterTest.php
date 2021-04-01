<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\NotFilter;
use Level23\Druid\Filters\SelectorFilter;

class NotFilterTest extends TestCase
{
    public function testFilter(): void
    {
        $filterName = new SelectorFilter('name', 'John');

        $filter = new NotFilter($filterName);

        $this->assertEquals([
            'type'  => 'not',
            'field' => $filterName->toArray(),
        ], $filter->toArray());
    }
}
