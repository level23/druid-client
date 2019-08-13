<?php
declare(strict_types=1);

namespace Level23\Druid\HavingFilters;

use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Filters\LogicalExpressionHavingFilterInterface;

class AndHavingFilter implements HavingFilterInterface, LogicalExpressionHavingFilterInterface
{
    /**
     * @var array|\Level23\Druid\Filters\FilterInterface[]|\Level23\Druid\HavingFilters\HavingFilterInterface[]
     */
    protected $filters;

    /**
     * AndHavingFilter constructor.
     *
     * @param HavingFilterInterface|FilterInterface ...$filters
     */
    public function __construct(...$filters)
    {
        $this->filters = $filters;
    }

    /**
     * Return the having filter as it can be used in a druid query.
     *
     * @return array
     */
    public function getHavingFilter(): array
    {
        $havingSpecs = [];

        foreach ($this->filters as $filter) {
            if ($filter instanceof FilterInterface) {
                $filter = new QueryHavingFilter($filter);
            }

            $havingSpecs[] = $filter->getHavingFilter();
        }

        return [
            'type'        => 'and',
            'havingSpecs' => $havingSpecs,
        ];
    }

    /**
     * Add an extra filter to our logical expression filter.
     *
     * @param \Level23\Druid\HavingFilters\HavingFilterInterface $having
     */
    public function addHavingFilter(HavingFilterInterface $having)
    {
        $this->filters[] = $having;
    }

    /**
     * Return all having filters which are used by this logical expression filter.
     *
     * @return array
     */
    public function getHavingFilters(): array
    {
        return $this->filters;
    }
}