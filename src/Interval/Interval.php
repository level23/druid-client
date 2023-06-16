<?php
declare(strict_types=1);

namespace Level23\Druid\Interval;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;

class Interval implements IntervalInterface
{
    protected DateTimeInterface $start;

    protected DateTimeInterface $stop;

    /**
     * Interval constructor.
     *
     * @param \DateTimeInterface|int|string      $start DateTime object, unix timestamp or string accepted by
     *                                                  DateTime::__construct
     * @param \DateTimeInterface|int|string|null $stop  DateTime object, unix timestamp or string accepted by
     *                                                  DateTime::__construct
     *
     * @throws \Exception
     */
    public function __construct(DateTimeInterface|int|string $start, DateTimeInterface|int|string $stop = null)
    {
        // Check if we received a "raw" interval string, like 2019-04-15T08:00:00.000Z/2019-04-15T09:00:00.000Z
        if (is_string($start) && $stop === null) {
            if (str_contains($start, '/')) {
                [$start, $stop] = explode('/', $start);
            } else {
                throw new InvalidArgumentException(
                    'Invalid interval given: ' . $start . '. ' .
                    'You should supply a valid interval (start and stop date) which is split by a forward slash (/).'
                );
            }
        }

        // Check if some gecko forgot the stop date.
        if ($stop === null) {
            throw new InvalidArgumentException(
                'Invalid parameters given for the interval() method. ' .
                'You should supply a valid start and stop value. This can be in string form ("start/stop"), or specify ' .
                'the start and stop parameters individually'
            );
        }

        if (!$start instanceof DateTimeInterface) {
            $start = new DateTime(is_numeric($start) ? "@$start" : $start);
        }

        if (!$stop instanceof DateTimeInterface) {
            $stop = new DateTime(is_numeric($stop) ? "@$stop" : $stop);
        }

        if ($stop < $start) {
            throw new InvalidArgumentException('The end date must be greater than the start date');
        }

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
        return $this->start->format('Y-m-d\TH:i:s.000\Z') . '/' . $this->stop->format('Y-m-d\TH:i:s.000\Z');
    }

    /**
     * @return \DateTimeInterface
     */
    public function getStart(): DateTimeInterface
    {
        return $this->start;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getStop(): DateTimeInterface
    {
        return $this->stop;
    }
}