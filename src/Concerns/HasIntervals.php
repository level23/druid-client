<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

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
     * @param \DateTime|string|int      $start DateTime object, unix timestamp or string accepted by DateTime::__construct
     *                                    or a raw interval format as returned by druid.
     * @param \DateTime|string|int|null $stop  DateTime object, unix timestamp or string accepted by DateTime::__construct
     *                                    or null if $start contains a raw interval string.
     *
     * @return $this
     * @throws \Exception
     */
    public function interval($start, $stop = null)
    {
        $this->intervals[] = new Interval($start, $stop);

        return $this;
    }

    /**
     * @return array|\Level23\Druid\Interval\IntervalInterface[]
     */
    public function getIntervals(): array
    {
        return $this->intervals;
    }
}