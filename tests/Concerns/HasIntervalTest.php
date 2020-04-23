<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Concerns;

use DateTime;
use Level23\Druid\DruidClient;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Tasks\IndexTaskBuilder;

class HasIntervalTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testInterval()
    {
        $builder = new IndexTaskBuilder(new DruidClient([]), 'dataSource');

        $start  = new DateTime('2019-01-01 00:00:00');
        $stop   = new DateTime('2019-01-31 23:59:59');
        $result = $builder->interval($start, $stop);

        $this->assertEquals($builder, $result);

        $interval = new Interval($start, $stop);
        $this->assertEquals($interval, $this->getProperty($builder, 'interval'));
    }
}
