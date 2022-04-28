<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

interface FilterInterface
{
    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|int|bool|array<mixed>>
     */
    public function toArray(): array;
}