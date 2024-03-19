<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Filters\IntervalFilter;

class IntervalFilterTest extends TestCase
{
    public function testFilter(): void
    {
        $intervals = [
            new Interval("2014-10-01T00:00:00.000Z", "2014-10-07T00:00:00.000Z"),
            new Interval("2014-11-15T00:00:00.000Z", "2014-11-16T00:00:00.000Z"),
        ];
        $filter    = new IntervalFilter('__time', $intervals);

        $this->assertEquals([
            'type'      => 'interval',
            'dimension' => '__time',
            'intervals' => [
                '2014-10-01T00:00:00.000Z/2014-10-07T00:00:00.000Z',
                '2014-11-15T00:00:00.000Z/2014-11-16T00:00:00.000Z',
            ],
        ], $filter->toArray());
    }
}
