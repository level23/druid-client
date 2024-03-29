<?php
declare(strict_types=1);

namespace Level23\Druid\Aggregations;

interface AggregatorInterface
{
    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array<string,string|bool|array<mixed>|int>
     */
    public function toArray(): array;
}