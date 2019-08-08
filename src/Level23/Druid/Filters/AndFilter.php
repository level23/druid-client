<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

class AndFilter implements FilterInterface
{
    /**
     * @var array|\Level23\Druid\Filters\FilterInterface[]
     */
    protected $filters;

    /**
     * AndFilter constructor.
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
            'type'   => 'and',
            'fields' => $fields,
        ];
    }
}