<?php
declare(strict_types=1);

namespace Level23\Druid\Interval;

use DateTime;
use InvalidArgumentException;

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

    /**
     * Interval constructor.
     *
     * @param \DateTime|string|int      $start DateTime object, unix timestamp or string accepted by
     *                                         DateTime::__construct
     * @param \DateTime|string|int|null $stop  DateTime object, unix timestamp or string accepted by
     *                                         DateTime::__construct
     *
     * @throws \Exception
     */
    public function __construct($start, $stop = null)
    {
        // Check if we received a "raw" interval string, like 2019-04-15T08:00:00.000Z/2019-04-15T09:00:00.000Z
        if (is_string($start) && $stop === null) {
            if (strpos($start, '/') !== false) {
                list($start, $stop) = explode('/', $start, 2);
            } else {
                throw new InvalidArgumentException(
                    'Invalid interval given: ' . $start . '. ' .
                    'You should supply a valid interval (start and stop date) which is split by a forward slash (/).'
                );
            }
        }

        if (!$start instanceof DateTime) {
            $start = new DateTime(is_numeric($start) ? "@$start" : $start);
        }

        if (!$stop instanceof DateTime) {
            $stop = new DateTime(is_numeric($stop) ? "@$stop" : $stop);
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
     * @return \DateTime
     */
    public function getStart(): DateTime
    {
        return $this->start;
    }

    /**
     * @return \DateTime
     */
    public function getStop(): DateTime
    {
        return $this->stop;
    }
}