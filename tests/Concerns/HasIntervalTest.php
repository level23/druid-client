<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Concerns;

use DateTime;
use tests\TestCase;
use Level23\Druid\DruidClient;
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
