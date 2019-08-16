<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

class OrFilter extends AndFilter
{
    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = parent::toArray();

        $result['type'] = 'or';

        return $result;
    }
}