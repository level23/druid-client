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
     * Make a aggregation collection of the given array.
     * We expect that the items in the array are objects of the AggregatorInterface
     *
     * @param array $aggregations
     *
     * @return \Level23\Druid\Collections\AggregationCollection
     */
    public static function make(array $aggregations): AggregationCollection
    {
        return new AggregationCollection(...$aggregations);
    }

    /**
     * Return an array representation of our items
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->items as $aggregator) {
            $result[] = $aggregator->getAggregator();
        }

        return $result;
    }
}