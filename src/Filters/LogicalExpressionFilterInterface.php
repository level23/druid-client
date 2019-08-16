<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

interface LogicalExpressionFilterInterface
{
    /**
     * Add an extra filter to our logical expression filter.
     *
     * @param \Level23\Druid\Filters\FilterInterface $filter
     */
    public function addFilter(FilterInterface $filter);
}