<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

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
 * dimensions is list of DimensionSpecs, making it possible to apply an extraction function if needed.
 *
 * @package Level23\Druid\Filters
 */
class ColumnComparisonFilter implements FilterInterface
{
    /**
     * @var string
     */
    protected $dimensionA;

    /**
     * @var string
     */
    protected $dimensionB;

    /**
     * @var string
     */
    protected $value;

    /**
     * ColumnComparisonFilter constructor.
     *
     * @param string $dimensionA
     * @param string $dimensionB
     */
    public function __construct(string $dimensionA, string $dimensionB)
    {
        $this->dimensionA = $dimensionA;
        $this->dimensionB = $dimensionB;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'       => 'columnComparison',
            'dimensions' => [
                $this->dimensionA,
                $this->dimensionB,
            ],
        ];
    }
}