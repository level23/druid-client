<?php

namespace Level23\Druid\Aggregations;

class SumAggregator extends MethodAggregator
{
    /**
     * Returns the method for the type aggregation
     *
     * @return string
     */
    protected function getMethod(): string
    {
        return 'sum';
    }
}