<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

interface ExtractionInterface
{
    /**
     * Return the Extraction Function so it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array;
}