<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\NullFilter;

class NullFilterTest extends TestCase
{
    public function testFilter(): void
    {
        $expected = [
            'type'   => 'null',
            'column' => 'name',
        ];

        $filter = new NullFilter('name');

        $this->assertEquals($expected, $filter->toArray());
    }
}
