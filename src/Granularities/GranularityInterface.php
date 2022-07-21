<?php
declare(strict_types=1);

namespace Level23\Druid\Granularities;

interface GranularityInterface
{
    /**
     * Return the granularity in array format so that we can use it in a druid request.
     *
     * @return array<string,string|string[]|bool>
     */
    public function toArray(): array;
}