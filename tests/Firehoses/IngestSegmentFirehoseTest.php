<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Firehoses;

use tests\TestCase;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Firehoses\IngestSegmentFirehose;

class IngestSegmentFirehoseTest extends TestCase
{
    public function testFirehose()
    {
        $interval = new Interval('12-04-2019', '15-04-2019');
        $firehose = new IngestSegmentFirehose('test', $interval);

        $this->assertEquals([
            'type'       => 'ingestSegment',
            'dataSource' => 'test',
            'interval'   => $interval->getInterval(),
        ], $firehose->toArray());
    }
}
