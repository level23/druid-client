<?php

namespace Level23\Druid\Aggregations;

interface AggregatorInterface
{
    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function getAggregator(): array;
}