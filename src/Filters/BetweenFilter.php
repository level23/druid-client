<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\Types\SortingOrder;
use Level23\Druid\Extractions\ExtractionInterface;

/**
 * Class BetweenFilter
 *
 * This filter will create a filter where the given dimension is greater than or equal to the given minValue, and
 * less than or equal to the given $maxValue.
 *
 * So in SQL syntax, this would be:
 * ```
 * WHERE dimension => $minValue AND dimension <= $maxValue
 * ```
 *
 * @package Level23\Druid\Filters
 */
class BetweenFilter implements FilterInterface
{
    protected string $dimension;

    protected string $minValue;

    protected string $maxValue;

    protected string $ordering;

    protected ?ExtractionInterface $extractionFunction;

    /**
     * BetweenFilter constructor.
     *
     * @param string                   $dimension         The dimension to filter on
     * @param int|string               $minValue
     * @param int|string               $maxValue
     * @param null|string              $ordering          Specifies the sorting order using when comparing values
     *                                                    against the bound.
     * @param ExtractionInterface|null $extractionFunction
     */
    public function __construct(
        string $dimension,
        $minValue,
        $maxValue,
        string $ordering = null,
        ExtractionInterface $extractionFunction = null
    ) {
        if (!is_null($ordering)) {
            $ordering = SortingOrder::validate($ordering);
        }

        $this->dimension          = $dimension;
        $this->ordering           = $ordering ?: (is_numeric($minValue) && is_numeric($maxValue) ? SortingOrder::NUMERIC : SortingOrder::LEXICOGRAPHIC);
        $this->extractionFunction = $extractionFunction;
        $this->minValue           = (string)$minValue;
        $this->maxValue           = (string)$maxValue;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|bool|array<string,string|int|bool|array<mixed>>>
     */
    public function toArray(): array
    {
        $result = [
            'type'        => 'bound',
            'dimension'   => $this->dimension,
            'ordering'    => $this->ordering,
            'lower'       => $this->minValue,
            'lowerStrict' => false,
            'upper'       => $this->maxValue,
            'upperStrict' => true,
        ];

        if ($this->extractionFunction) {
            $result['extractionFn'] = $this->extractionFunction->toArray();
        }

        return $result;
    }
}