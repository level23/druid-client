<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Interval;

use DateTime;
use Level23\Druid\Interval\Interval;
use tests\TestCase;

class IntervalTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testInterval()
    {
        $format   = 'Y-m-d\TH:i:s.000\Z';
        $start    = new DateTime('yesterday');
        $stop     = new DateTime('now - 1 minute');
        $interval = new Interval($start->getTimestamp(), $stop->getTimestamp());

        $this->assertEquals(
            $start->format($format) . '/' . $stop->format($format),
            $interval->getInterval()
        );

        $this->assertEquals($interval->getStart()->format($format), $start->format($format));
        $this->assertEquals($interval->getStop()->format($format), $stop->format($format));
    }
}
