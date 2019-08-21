<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use tests\TestCase;
use Level23\Druid\Filters\TrueFilter;

class TrueFilterTest extends TestCase
{
    public function testFilter()
    {
        $filter = new TrueFilter();

        $this->assertEquals([
            'type' => 'true',
        ], $filter->toArray());
    }
}