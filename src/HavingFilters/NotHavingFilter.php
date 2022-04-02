<?php
declare(strict_types=1);

namespace Level23\Druid\HavingFilters;

class NotHavingFilter implements HavingFilterInterface
{
    protected HavingFilterInterface $filter;

    /**
     * NotHavingFilter constructor.
     *
     * @param HavingFilterInterface $filter
     */
    public function __construct(HavingFilterInterface $filter)
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
            'type'       => 'not',
            'havingSpec' => $this->filter->toArray(),
        ];
    }
}