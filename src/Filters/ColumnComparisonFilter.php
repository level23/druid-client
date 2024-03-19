<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\Dimensions\DimensionInterface;

/**
 * Class ColumnComparisonFilter
 *
 * The column comparison filter is similar to the selector filter,
 * but instead compares dimensions to each other. For example:
 *
 * "filter": { "type": "columnComparison", "dimensions": [<dimension_a>, <dimension_b>] }
 *
 * This is the equivalent of WHERE <dimension_a> = <dimension_b>.
 *
 * dimensions is list of DimensionSpecs
 *
 * @package Level23\Druid\Filters
 */
class ColumnComparisonFilter implements FilterInterface
{
    protected DimensionInterface $dimensionA;

    protected DimensionInterface $dimensionB;

    protected string $value;

    /**
     * ColumnComparisonFilter constructor.
     *
     * @param DimensionInterface $dimensionA
     * @param DimensionInterface $dimensionB
     */
    public function __construct(DimensionInterface $dimensionA, DimensionInterface $dimensionB)
    {
        $this->dimensionA = $dimensionA;
        $this->dimensionB = $dimensionB;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|array<array<string,string|array<mixed>>>>
     */
    public function toArray(): array
    {
        return [
            'type'       => 'columnComparison',
            'dimensions' => [
                $this->dimensionA->toArray(),
                $this->dimensionB->toArray(),
            ],
        ];
    }
}