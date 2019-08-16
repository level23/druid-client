<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use DateTime;
use Level23\Druid\Interval\Interval;

trait HasIntervals
{
    /**
     * @var array|\Level23\Druid\Interval\IntervalInterface[]
     */
    protected $intervals = [];

    /**
     * Add an interval, eg the date where we want to select data from.
     *
     * @param \DateTime|string|int $start DateTime object, unix timestamp or string accepted by DateTime::__construct
     * @param \DateTime|string|int $stop  DateTime object, unix timestamp or string accepted by DateTime::__construct
     *
     * @return $this
     * @throws \Exception
     */
    public function interval($start, $stop)
    {
        if (!$start instanceof DateTime) {
            $start = new DateTime(is_numeric($start) ? "@$start" : $start);
        }

        if (!$stop instanceof DateTime) {
            $stop = new DateTime(is_numeric($stop) ? "@$stop" : $stop);
        }

        $this->intervals[] = new Interval($start, $stop);

        return $this;
    }

    /**
     * @return array|\Level23\Druid\Interval\IntervalInterface[]
     */
    public function getIntervals()
    {
        return $this->intervals;
    }
}