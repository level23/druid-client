<?php
declare(strict_types=1);

namespace Level23\Druid\SearchFilters;

interface SearchFilterInterface
{
    /**
     * @return array<string,string|string[]|bool>
     */
    public function toArray(): array;
}