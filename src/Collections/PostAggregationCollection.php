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
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->items as $postAggregation) {
            $result[] = $postAggregation->getPostAggregator();
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
        return PostAggregatorInterface::class;
    }
}