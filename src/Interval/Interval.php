<?php
declare(strict_types=1);

namespace Level23\Druid\Interval;

use DateTime;

class Interval implements IntervalInterface
{
    /**
     * @var DateTime
     */
    protected $start;

    /**
     * @var DateTime
     */
    protected $stop;

    public function __construct(DateTime $start, DateTime $stop)
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
        return $this->start->format(DateTime::ATOM) . '/' . $this->stop->format(DateTime::ATOM);
    }

    /**
     * @return \DateTime
     */
    public function getStart(): \DateTime
    {
        return $this->start;
    }

    /**
     * @return \DateTime
     */
    public function getStop(): \DateTime
    {
        return $this->stop;
    }
}