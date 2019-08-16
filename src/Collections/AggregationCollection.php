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
     * @param \Level23\Druid\Aggregations\AggregatorInterface $aggregator
     */
    public function add(AggregatorInterface $aggregator)
    {
        $this->items[] = $aggregator;
    }

    /**
     * Return an array representation of our items
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function(AggregatorInterface $item) {
            return $item->toArray();
        }, $this->items);
    }
}