<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\RegexFilter;

class RegexFilterTest extends TestCase
{
    public function testFilter(): void
    {
        $expected = [
            'type'      => 'regex',
            'dimension' => 'name',
            'pattern'   => '^[a-z]*$',
        ];

        $filter = new RegexFilter('name', '^[a-z]*$');

        $this->assertEquals($expected, $filter->toArray());
    }
}
