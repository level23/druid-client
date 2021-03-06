<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

class LeastPostAggregator extends MethodPostAggregator
{
    /**
     * Returns the method for the type aggregation
     *
     * @return string
     */
    protected function getMethod(): string
    {
        return 'least';
    }
}