<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

/**
 * Class TrueFilter
 *
 * The true filter is a filter which matches all values.
 * It can be used to temporarily disable other filters without removing the filter.
 *
 * @package Level23\Druid\Filters
 */
class TrueFilter implements FilterInterface
{
    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'type' => 'true',
        ];
    }
}