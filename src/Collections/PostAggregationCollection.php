<?php
declare(strict_types=1);

namespace Level23\Druid\Collections;

use Level23\Druid\PostAggregations\PostAggregatorInterface;

/**
 * @extends \Level23\Druid\Collections\BaseCollection<PostAggregatorInterface>
 */
class PostAggregationCollection extends BaseCollection
{
    public function __construct(PostAggregatorInterface ...$postAggregations)
    {
        $this->items = array_values($postAggregations);
    }

    /**
     * Return an array representation of our items
     *
     * @return array<array<string,string|array<mixed>>>
     */
    public function toArray(): array
    {
        return array_map(fn(PostAggregatorInterface $item) => $item->toArray(), $this->items);
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