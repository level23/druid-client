<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use tests\TestCase;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Filters\IntervalFilter;
use Level23\Druid\Extractions\LookupExtraction;

class IntervalFilterTest extends TestCase
{
    public function testFilter()
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

    public function testExtractionFunction()
    {
        $extractionFunction = new LookupExtraction(
            'singup_by_member', false
        );

        $intervals = [
            new Interval("2014-10-01T00:00:00.000Z", "2014-10-07T00:00:00.000Z"),
            new Interval("2014-11-15T00:00:00.000Z", "2014-11-16T00:00:00.000Z"),
        ];
        $filter    = new IntervalFilter('__time', $intervals, $extractionFunction);

        $this->assertEquals([
            'type'         => 'interval',
            'dimension'    => '__time',
            'intervals'    => [
                '2014-10-01T00:00:00.000Z/2014-10-07T00:00:00.000Z',
                '2014-11-15T00:00:00.000Z/2014-11-16T00:00:00.000Z',
            ],
            'extractionFn' => $extractionFunction->toArray(),
        ], $filter->toArray());
    }
}