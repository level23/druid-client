<?php
declare(strict_types=1);

namespace Level23\Druid\Collections;

use Level23\Druid\Aggregations\AggregatorInterface;

class AggregationCollection extends BaseCollection
{
    /**
     * We only accept objects of this type.
     *
     * @return string
     */
    public function getType(): string
    {
        return AggregatorInterface::class;
    }

    public function __construct(AggregatorInterface ...$aggregations)
    {
        $this->items = $aggregations;
    }

    /**
     * Return an array representation of our items
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(fn(AggregatorInterface $item) => $item->toArray(), $this->items);
    }
}