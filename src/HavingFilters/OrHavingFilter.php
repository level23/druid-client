<?php
declare(strict_types=1);

namespace Level23\Druid\HavingFilters;

class OrHavingFilter extends AndHavingFilter
{
    /**
     * Return the having filter as it can be used in a druid query.
     *
     * @return array<string,string|array<array<string,string|float|array<mixed>|bool>>>
     */
    public function toArray(): array
    {
        $result = parent::toArray();

        $result['type'] = 'or';

        return $result;
    }
}