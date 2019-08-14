<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use DateTime;
use Exception;
use Level23\Druid\Exceptions\DruidException;
use Level23\Druid\Interval\Interval;

trait HasIntervals
{
    /**
     * @var array|\Level23\Druid\Interval\IntervalInterface[]
     */
    protected $intervals = [];

    /**
     * Add an interval, eg the date where we want to select data from.
     * This can be an Carbon or DateTime object, or a string which can be parsed to a datetime.
     *
     * @param \DateTime|string $start
     * @param \DateTime|string $stop
     *
     * @return $this
     * @throws \Level23\Druid\Exceptions\DruidException
     */
    public function interval($start, $stop)
    {
        try {
            if (!$start instanceof DateTime) {
                $start = new DateTime($start);
            }

            if (!$stop instanceof DateTime) {
                $stop = new DateTime($stop);
            }
        } catch (Exception $exception) {
            throw new DruidException($exception->getMessage(), 0, $exception);
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