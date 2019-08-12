<?php
declare(strict_types=1);

namespace Level23\Druid\Interval;

use Carbon\Carbon;

class Interval implements IntervalInterface
{
    /**
     * @var \Carbon\Carbon
     */
    protected $start;

    /**
     * @var \Carbon\Carbon
     */
    protected $stop;

    public function __construct(Carbon $start, Carbon $stop)
    {

        $this->start = $start;
        $this->stop  = $stop;
    }

    /**
     * Return the interval in ISO-8601 format.
     * For example: "2012-01-01T00:00:00.000/2012-01-03T00:00:00.000"
     *
     * @return string
     */
    public function getInterval(): string
    {
        return $this->start->toIso8601String() . '/' . $this->stop->toIso8601String();
    }
}