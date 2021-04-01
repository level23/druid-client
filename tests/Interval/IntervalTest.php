<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Interval;

use DateTime;
use InvalidArgumentException;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Interval\Interval;

class IntervalTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testInterval(): void
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
    public function testIntervalWithStrings($start, $stop = null, $expectException = false): void
    {
        $format = 'Y-m-d\TH:i:s.000\Z';

        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $interval = new Interval($start, $stop);

        if (strpos($start, '/') !== false) {
            [$start, $stop] = explode('/', $start);
        }

        $startObj = new DateTime((string)$start);
        $stopObj  = new DateTime((string)$stop);

        $this->assertEquals($interval->getStart()->format($format), $startObj->format($format));
        $this->assertEquals($interval->getStop()->format($format), $stopObj->format($format));
    }

    public function testWithEndBeforeStart(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The end date must be greater than the start date');

        new Interval("2019-04-15", "2019-04-11");
    }

    /**
     * @throws \Exception
     */
    public function testWithoutEndDate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid parameters given for the interval() method.');

        new Interval(new DateTime());
    }
}
