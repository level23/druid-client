<?php
declare(strict_types=1);

namespace Level23\Druid\HavingFilters;

interface HavingFilterInterface
{
    /**
     * Return the having filter as it can be used in a druid query.
     *
     * @return array
     */
    public function getHavingFilter(): array;
}