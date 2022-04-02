<?php
declare(strict_types=1);

namespace Level23\Druid\Dimensions;

interface DimensionInterface
{
    const OUTPUT_TYPE_STRING = "string";

    /**
     * Get the dimension in array format, so we can use it for a druid query.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Return the name of the dimension which is selected.
     *
     * @return string
     */
    public function getDimension(): string;

    /**
     * Return the output name of this dimension
     *
     * @return string
     */
    public function getOutputName(): string;
}