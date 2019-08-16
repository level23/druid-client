<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use Level23\Druid\Filters\TrueFilter;
use tests\TestCase;

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