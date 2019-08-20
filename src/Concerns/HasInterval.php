<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Level23\Druid\Interval\Interval;

trait HasInterval
{
    /**
     * @var \Level23\Druid\Interval\Interval|null
     */
    protected $interval;

    /**
     * Set the interval, eg the date where we want to select data from.
     *
     * @param \DateTime|string|int $start DateTime object, unix timestamp or string accepted by DateTime::__construct
     * @param \DateTime|string|int $stop  DateTime object, unix timestamp or string accepted by DateTime::__construct
     *
     * @return $this
     * @throws \Exception
     */
    public function interval($start, $stop)
    {
        $this->interval = new Interval($start, $stop);

        return $this;
    }

    /**
     * @return \Level23\Druid\Interval\Interval|null
     */
    public function getInterval(): ?Interval
    {
        return $this->interval;
    }
}