<?php
declare(strict_types=1);

namespace Level23\Druid\Interval;

use DateTime;

interface IntervalInterface
{
    /**
     * Return the interval in ISO-8601 format.
     * For example: "2012-01-01T00:00:00.000/2012-01-03T00:00:00.000"
     *
     * @return string
     */
    public function getInterval(): string;

    /**
     * Return the start date of the interval.
     *
     * @return \DateTime
     */
    public function getStart(): DateTime;

    /**
     * Return the stop date of the interval.
     *
     * @return \DateTime
     */
    public function getStop(): DateTime;
}