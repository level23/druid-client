<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Tasks;

use Mockery;
use tests\TestCase;
use Level23\Druid\Tasks\IndexTask;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Firehoses\IngestSegmentFirehose;

class IndexTaskTest extends TestCase
{
    public function testTask()
    {
        $dataSource = 'people';
        $interval = new Interval('12-02-2019', '13-02-2019');

        $firehose = Mockery::mock(IngestSegmentFirehose::class, [$dataSource, $interval]);

//        $task = new IndexTask(
        //            $dataSource,
        //            $firehose
        //        )
    }
}