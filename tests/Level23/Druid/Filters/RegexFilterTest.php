<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use Level23\Druid\Filters\RegexFilter;
use tests\TestCase;

class RegexFilterTest extends TestCase
{
    public function testFilter()
    {
        $filter = new RegexFilter('name', '^[a-z]*$');

        $this->assertEquals([
            'type'      => 'regex',
            'dimension' => 'name',
            'pattern'   => '^[a-z]*$',
        ], $filter->getFilter());
    }
}