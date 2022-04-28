<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

class NotFilter implements FilterInterface
{
    protected FilterInterface $filter;

    /**
     * NotFilter constructor.
     *
     * @param \Level23\Druid\Filters\FilterInterface $filter
     */
    public function __construct(FilterInterface $filter)
    {
        $this->filter = $filter;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|array<string,string|int|bool|array<mixed>>>
     */
    public function toArray(): array
    {
        return [
            'type'  => 'not',
            'field' => $this->filter->toArray(),
        ];
    }
}