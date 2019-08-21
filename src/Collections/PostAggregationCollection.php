<?php
declare(strict_types=1);

namespace Level23\Druid\Collections;

use Level23\Druid\PostAggregations\PostAggregatorInterface;

class PostAggregationCollection extends BaseCollection
{
    public function __construct(PostAggregatorInterface ...$postAggregations)
    {
        $this->items = $postAggregations;
    }

    /**
     * Return an array representation of our items
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function (PostAggregatorInterface $item) {
            return $item->toArray();
        }, $this->items);
    }

    /**
     * We only accept objects of this type.
     *
     * @return string
     */
    public function getType(): string
    {
        return PostAggregatorInterface::class;
    }
}