<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

interface PostAggregatorInterface
{
    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function getPostAggregator(): array;
}