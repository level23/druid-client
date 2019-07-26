<?php

namespace Level23\Druid\Filters;

class OrFilter implements FilterInterface
{
    /**
     * @var array|\Level23\Druid\Filters\FilterInterface[]
     */
    protected $filters;

    /**
     * OrFilter constructor.
     *
     * @param array $filters List of DruidFilter classes.
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
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
            'type'   => 'or',
            'fields' => $fields,
        ];
    }
}