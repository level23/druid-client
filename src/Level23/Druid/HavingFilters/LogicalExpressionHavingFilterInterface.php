<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\HavingFilters\HavingFilterInterface;

interface LogicalExpressionHavingFilterInterface
{
    /**
     * Add an extra filter to our logical expression filter.
     *
     * @param \Level23\Druid\HavingFilters\HavingFilterInterface $having
     *
     * @return $this
     */
    public function addHavingFilter(HavingFilterInterface $having);

    /**
     * Return all having filters which are used by this logical expression filter.
     *
     * @return array
     */
    public function getHavingFilters(): array;
}