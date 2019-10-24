<?php
declare(strict_types=1);

namespace Level23\Druid\SearchFilters;

interface SearchFilterInterface
{
    public function toArray(): array;
}