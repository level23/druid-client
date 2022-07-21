<?php
declare(strict_types=1);

namespace Level23\Druid\VirtualColumns;

interface VirtualColumnInterface
{
    /**
     * Return the virtual column as it can be used in a druid query.
     *
     * @return array<string,string>
     */
    public function toArray(): array;
}