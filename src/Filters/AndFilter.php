<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

class AndFilter implements FilterInterface, LogicalExpressionFilterInterface
{
    /**
     * @var array|\Level23\Druid\Filters\FilterInterface[]
     */
    protected array $filters;

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
    public function toArray(): array
    {
        $fields = [];

        foreach ($this->filters as $filter) {
            $fields[] = $filter->toArray();
        }

        return [
            'type'   => 'and',
            'fields' => $fields,
        ];
    }
}