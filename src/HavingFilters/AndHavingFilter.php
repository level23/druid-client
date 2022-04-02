<?php
declare(strict_types=1);

namespace Level23\Druid\HavingFilters;

class AndHavingFilter implements HavingFilterInterface, LogicalExpressionHavingFilterInterface
{
    /**
     * @var array|\Level23\Druid\HavingFilters\HavingFilterInterface[]
     */
    protected array $filters;

    /**
     * AndHavingFilter constructor.
     *
     * @param array|\Level23\Druid\HavingFilters\HavingFilterInterface[] $filters
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * Return the having filter as it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'        => 'and',
            'havingSpecs' => array_map(fn(HavingFilterInterface $filter) => $filter->toArray(), $this->filters),
        ];
    }

    /**
     * Add an extra filter to our logical expression filter.
     *
     * @param \Level23\Druid\HavingFilters\HavingFilterInterface $having
     */
    public function addHavingFilter(HavingFilterInterface $having): void
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