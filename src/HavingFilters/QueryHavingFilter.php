<?php
declare(strict_types=1);

namespace Level23\Druid\HavingFilters;

use Level23\Druid\Filters\FilterInterface;

class QueryHavingFilter implements HavingFilterInterface
{
    protected FilterInterface $filter;

    public function __construct(FilterInterface $filter)
    {
        $this->filter = $filter;
    }

    /**
     * Return the having filter as it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'   => 'filter',
            'filter' => $this->filter->toArray(),
        ];
    }
}