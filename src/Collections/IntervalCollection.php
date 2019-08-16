<?php
declare(strict_types=1);

namespace Level23\Druid\Collections;

use Level23\Druid\Interval\IntervalInterface;

class IntervalCollection extends BaseCollection
{
    /**
     * IntervalCollection constructor.
     *
     * @param \Level23\Druid\Interval\IntervalInterface ...$intervals
     */
    public function __construct(IntervalInterface ...$intervals)
    {
        $this->items = $intervals;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->items as $interval) {
            $result[] = $interval->getInterval();
        }

        return $result;
    }

    /**
     * We only accept objects of this type.
     *
     * @return string
     */
    public function getType(): string
    {
        return IntervalInterface::class;
    }
}