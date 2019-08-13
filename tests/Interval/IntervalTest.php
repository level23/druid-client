<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Interval;

use DateTime;
use Level23\Druid\Interval\Interval;
use tests\TestCase;

class IntervalTest extends TestCase
{
    public function testInterval()
    {
        $start    = new DateTime('yesterday');
        $stop     = new DateTime('now - 1 minute');
        $interval = new Interval($start, $stop);

        $this->assertEquals(
            $start->format(DateTime::ATOM) . '/' . $stop->format(DateTime::ATOM),
            $interval->getInterval()
        );
    }
}
