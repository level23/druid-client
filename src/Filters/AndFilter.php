<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

class AndFilter implements FilterInterface, LogicalExpressionFilterInterface
{
    /**
     * @var array|\Level23\Druid\Filters\FilterInterface[]
     */
    protected $filters;

    /**
     * AndFilter constructor.
     *
     * @param array|\Level23\Druid\Filters\FilterInterface[] $filters List of DruidFilter classes.
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * @param \Level23\Druid\Filters\FilterInterface $filter
     */
    public function addFilter(FilterInterface $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array
     */
    public function getFilter(): array
    {
        $fields = [];

        foreach ($this->filters as $filter) {
            $fields[] = $filter->getFilter();
        }

        return [
            'type'   => 'and',
            'fields' => $fields,
        ];
    }

    /**
     * Return all filters which are used by this logical expression filter.
     *
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}