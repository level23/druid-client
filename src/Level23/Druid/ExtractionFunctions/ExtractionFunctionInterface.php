<?php

namespace Level23\Druid\ExtractionFunctions;

interface ExtractionFunctionInterface
{
    /**
     * Return the Extraction Function so it can be used in a druid query.
     * @return array
     */
    public function getExtractionFunction(): array;
}