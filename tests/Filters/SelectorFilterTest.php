<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\SelectorFilter;

class SelectorFilterTest extends TestCase
{
    public function testFilter(): void
    {
        $expected = [
            'type'      => 'selector',
            'dimension' => 'name',
            'value'     => 'John',
        ];

        $filter = new SelectorFilter('name', 'John');

        $this->assertEquals($expected, $filter->toArray());
    }
}
