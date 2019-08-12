<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use Level23\Druid\Filters\IntervalFilter;
use tests\TestCase;

class IntervalFilterTest extends TestCase
{
    public function testFilter()
    {
        $intervals = [
            "2014-10-01T00:00:00.000Z/2014-10-07T00:00:00.000Z",
            "2014-11-15T00:00:00.000Z/2014-11-16T00:00:00.000Z",
        ];
        $filter    = new IntervalFilter('__time', $intervals);

        $this->assertEquals([
            'type' => 'interval',
            'dimension' => '__time',
            'intervals' => $intervals,
        ], $filter->getFilter());
    }
}