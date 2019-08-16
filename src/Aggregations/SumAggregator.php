<?php

namespace Level23\Druid\Aggregations;

use InvalidArgumentException;
use Level23\Druid\Types\DataType;

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