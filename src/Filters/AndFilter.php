<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

class AndFilter implements FilterInterface, LogicalExpressionFilterInterface
{
    /**
     * @var \Level23\Druid\Filters\FilterInterface[]
     */
    protected array $filters;

    /**
     * AndFilter constructor.
     *
     * @param \Level23\Druid\Filters\FilterInterface[] $filters List of DruidFilter classes.
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * @param \Level23\Druid\Filters\FilterInterface $filter
     */
    public function addFilter(FilterInterface $filter): void
    {
        $this->filters[] = $filter;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|array<array<string,string|int|bool|array<mixed>>>>
     */
    public function toArray(): array
    {
        return [
            'type'   => 'and',
            'fields' => array_map(fn(FilterInterface $filter) => $filter->toArray(), $this->filters),
        ];
    }
}