<?php
declare(strict_types=1);

namespace Level23\Druid\Collections;

use Level23\Druid\Interval\IntervalInterface;

/**
 * @extends \Level23\Druid\Collections\BaseCollection<IntervalInterface>
 */
class IntervalCollection extends BaseCollection
{
    /**
     * IntervalCollection constructor.
     *
     * @param \Level23\Druid\Interval\IntervalInterface ...$intervals
     */
    public function __construct(IntervalInterface ...$intervals)
    {
        $this->items = array_values($intervals);
    }

    /**
     * Return an array representation of our items
     *
     * @return array<int,string>
     */
    public function toArray(): array
    {
        return array_map(fn(IntervalInterface $item) => $item->getInterval(), $this->items);
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