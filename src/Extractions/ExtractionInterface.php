<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

interface ExtractionInterface
{
    /**
     * Return the Extraction Function, so it can be used in a druid query.
     *
     * @return array<string,string|int|bool|array<mixed>>
     */
    public function toArray(): array;
}