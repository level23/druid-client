<?php
declare(strict_types=1);

namespace Level23\Druid\HavingFilters;

use Level23\Druid\Filters\FilterInterface;

class NotHavingFilter implements HavingFilterInterface
{
    /**
     * @var \Level23\Druid\Filters\FilterInterface|\Level23\Druid\HavingFilters\HavingFilterInterface
     */
    protected $filter;

    /**
     * NotHavingFilter constructor.
     *
     * @param HavingFilterInterface|FilterInterface $filter
     */
    public function __construct($filter)
    {
        $this->filter = $filter;
    }

    /**
     * Return the having filter as it can be used in a druid query.
     *
     * @return array
     */
    public function getHavingFilter(): array
    {
        if ($this->filter instanceof HavingFilterInterface) {
            $havingSpec = $this->filter->getHavingFilter();
        } else {
            $havingSpec = [
                'type'   => 'filter',
                'filter' => $this->filter->getFilter(),
            ];
        }

        return [
            'type'       => 'not',
            'havingSpec' => $havingSpec,
        ];
    }
}