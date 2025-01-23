<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\Types\DataType;
use Level23\Druid\Types\SortingOrder;
use Level23\Druid\Types\BoundOperator;

/**
 * Class RangeFilter
 *
 * The range filter is a replacement for the bound filter. It compares against any type of column and is designed to
 * have has more SQL compliant behavior than the bound filter. It won't match null values, even if you
 * don't specify a lower bound.
 *
 * @package Level23\Druid\Filters
 */
class RangeFilter implements FilterInterface
{
    protected string $column;

    protected BoundOperator $operator;

    protected string|int|float $value;

    protected SortingOrder $ordering;

    protected DataType $valueType;

    /**
     * BoundFilter constructor.
     *
     * @param string                   $column            Input column or virtual column name to filter.
     *
     * @param string|BoundOperator     $operator          The operator to use. Use ">", ">=", "<", or "<=" Or use the
     *                                                    BoundOperator constants.
     * @param string|int|float         $value             The value to compare with. This can either be a numeric or a
     *                                                    string.
     * @param DataType|null            $valueType         String specifying the type of bounds to match. The valueType
     *                                                    determines how Druid interprets the matchValue to assist in
     *                                                    converting to the type of the matched column and also defines
     *                                                    the type of comparison used when matching values.
     */
    public function __construct(
        string $column,
        string|BoundOperator $operator,
        string|int|float $value,
        ?DataType $valueType = null
    ) {
        $this->column   = $column;
        $this->operator = is_string($operator) ? BoundOperator::from($operator) : $operator;
        $this->value    = $value;

        if (is_null($valueType)) {

            if (is_int($value)) {
                $valueType = DataType::LONG;
            } elseif (is_float($value)) {
                $valueType = DataType::DOUBLE;
            } else {
                $valueType = DataType::STRING;
            }
        }

        $this->valueType = $valueType;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|int|float|bool|array<string,string|int|bool|array<mixed>>>
     */
    public function toArray(): array
    {
        $result = [
            'type'           => 'range',
            'column'         => $this->column,
            'matchValueType' => $this->valueType->value,
        ];

        switch ($this->operator) {
            case BoundOperator::GE:
                $result['lower']     = $this->value;
                $result['lowerOpen'] = false;
                break;
            case BoundOperator::GT:
                $result['lower']     = $this->value;
                $result['lowerOpen'] = true;
                break;
            case BoundOperator::LE:
                $result['upper']     = $this->value;
                $result['upperOpen'] = false;
                break;
            case BoundOperator::LT:
                $result['upper']     = $this->value;
                $result['upperOpen'] = true;
                break;
        }

        return $result;
    }
}