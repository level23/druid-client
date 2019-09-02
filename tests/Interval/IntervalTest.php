<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Interval;

use DateTime;
use tests\TestCase;
use InvalidArgumentException;
use Level23\Druid\Interval\Interval;

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

    /**
     * @param string      $start
     * @param string|null $stop
     * @param bool        $expectException
     *
     * @throws \Exception
     * @testWith ["now/tomorrow"]
     *           ["2019-04-15T08:00:00.000Z/2019-04-15T09:00:00.000Z"]
     *           ["2019-04-15T08:00:00+02:00/2019-04-15T09:00:00+02:00"]
     *           ["2019-04-15/2019-04-15"]
     *           ["2019-04-15", "2019-04-15"]
     *           ["2019-04-15", null, true]
     */
    public function testIntervalWithStrings($start, $stop = null, $expectException = false)
    {
        $format = 'Y-m-d\TH:i:s.000\Z';

        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $interval = new Interval($start, $stop);

        if (strpos($start, '/') !== false) {
            list($start, $stop) = explode('/', $start, 2);
        }

        $startObj = new DateTime($start);
        $stopObj  = new DateTime($stop);

        $this->assertEquals($interval->getStart()->format($format), $startObj->format($format));
        $this->assertEquals($interval->getStop()->format($format), $stopObj->format($format));
    }

    public function testWithEndBeforeStart()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The end date must be greater than the start date');

        new Interval("2019-04-15", "2019-04-11");
    }
}
