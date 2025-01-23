<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\Types\DataType;

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
    protected string $column;

    protected int|string|float $minValue;

    protected int|string|float $maxValue;

    protected DataType $valueType;

    /**
     * BetweenFilter constructor.
     *
     * @param string           $column    The dimension to filter on
     * @param int|string|float $minValue
     * @param int|string|float $maxValue
     * @param DataType|null    $valueType String specifying the type of bounds to match. The valueType determines how
     *                                    Druid interprets the matchValue to assist in converting to the type of the
     *                                    matched column and also defines the type of comparison used when matching
     *                                    values.
     */
    public function __construct(
        string $column,
        int|float|string $minValue,
        int|float|string $maxValue,
        ?DataType $valueType = null
    ) {
        if (is_null($valueType)) {

            if (is_int($minValue)) {
                $valueType = DataType::LONG;
            } elseif (is_float($minValue)) {
                $valueType = DataType::DOUBLE;
            } else {
                $valueType = DataType::STRING;
            }
        }

        $this->column    = $column;
        $this->valueType = $valueType;
        $this->minValue  = $minValue;
        $this->maxValue  = $maxValue;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|bool|float>
     */
    public function toArray(): array
    {
        return [
            'type'           => 'range',
            'column'         => $this->column,
            'matchValueType' => $this->valueType->value,
            'lower'          => $this->minValue,
            'lowerOpen'      => false, // >=
            'upper'          => $this->maxValue,
            'upperOpen'      => true, // <
        ];
    }
}