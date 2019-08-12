<?php
declare(strict_types=1);

namespace Level23\Druid\HavingFilters;

class OrHavingFilter extends AndHavingFilter
{
    /**
     * Return the having filter as it can be used in a druid query.
     *
     * @return array
     */
    public function getHavingFilter(): array
    {
        $result = parent::getHavingFilter();

        $result['type'] = 'or';

        return $result;
    }
}