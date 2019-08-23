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
    /**
     * @var string
     */
    protected $dimension;

    /**
     * @var int|string
     */
    protected $minValue;

    /**
     * @var int|string
     */
    protected $maxValue;

    /**
     * @var \Level23\Druid\Types\SortingOrder|string|null
     */
    protected $ordering;

    /**
     * @var \Level23\Druid\Extractions\ExtractionInterface|null
     */
    protected $extractionFunction;

    /**
     * BetweenFilter constructor.
     *
     * @param string                   $dimension         The dimension to filter on
     * @param int|string               $minValue
     * @param int|string               $maxValue
     * @param SortingOrder|null|string $ordering          Specifies the sorting order to use when comparing values
     *                                                    against the bound.
     * @param ExtractionInterface|null $extractionFunction
     */
    public function __construct(
        string $dimension,
        $minValue,
        $maxValue,
        $ordering = null,
        ExtractionInterface $extractionFunction = null
    ) {
        SortingOrder::validate($ordering);

        $this->dimension          = $dimension;
        $this->ordering           = $ordering ?: (is_numeric($minValue) && is_numeric($maxValue) ? SortingOrder::NUMERIC() : SortingOrder::LEXICOGRAPHIC());
        $this->extractionFunction = $extractionFunction;
        $this->minValue           = $minValue;
        $this->maxValue           = $maxValue;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'type'        => 'bound',
            'dimension'   => $this->dimension,
            'ordering'    => (string)$this->ordering,
            'lower'       => (string)$this->minValue,
            'lowerStrict' => false,
            'upper'       => (string)$this->maxValue,
            'upperStrict' => false,
        ];

        if ($this->extractionFunction) {
            $result['extractionFn'] = $this->extractionFunction->toArray();
        }

        return $result;
    }
}