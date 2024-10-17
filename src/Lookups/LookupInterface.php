<?php
declare(strict_types=1);

namespace Level23\Druid\Lookups;

interface LookupInterface
{
    /**
     * Return the lookup in array format how we can send it to druid.
     *
     * @return array<string,string|array<mixed>|bool|int>
     */
    public function toArray(): array;
}