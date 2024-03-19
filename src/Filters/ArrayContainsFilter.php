<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\Types\DataType;

class ArrayContainsFilter implements FilterInterface
{
    protected string $column;

    protected string|int|float|null $value;

    /**
     * @var \Level23\Druid\Types\DataType
     */
    protected DataType $matchValueType;

    /**
     * Equality Filter constructor.
     *
     * @param string                             $column         Input column or virtual column name to filter.
     * @param string|int|float|null              $value          Array element value to match. This value can be null.
     * @param \Level23\Druid\Types\DataType|null $matchValueType The type of value to match. When not given, we will
     *                                                           auto-detect the value based on the given value.
     */
    public function __construct(
        string $column,
        string|int|float|null $value,
        DataType $matchValueType = null
    ) {
        if (is_null($matchValueType)) {

            if (is_int($value)) {
                $matchValueType = DataType::LONG;
            } elseif (is_float($value)) {
                $matchValueType = DataType::DOUBLE;
            } else {
                $matchValueType = DataType::STRING;
            }
        }

        $this->value          = $value;
        $this->matchValueType = $matchValueType;
        $this->column         = $column;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|int|float|array<string,string|int|bool|array<mixed>>>
     */
    public function toArray(): array
    {
        return [
            'type'                  => 'arrayContainsElement',
            'column'                => $this->column,
            'elementMatchValueType' => $this->matchValueType->value,
            'elementMatchValue'     => $this->value,
        ];
    }
}