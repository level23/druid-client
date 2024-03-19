<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use ValueError;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\DataType;
use Level23\Druid\Filters\RangeFilter;

class RangeFilterTest extends TestCase
{
    /**
     * @return array<array<string|null|bool|float>>
     */
    public static function dataProvider(): array
    {
        $fields      = ['name'];
        $values      = [18, 'foo', 16.4];
        $operators   = ['>', '>=', '<', '<='];

        $result = [];

        foreach ($fields as $dimension) {
            foreach ($values as $value) {
                foreach ($operators as $operator) {
                    $result[] = [$dimension, $operator, $value];
                }
            }
        }

        return $result;
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string           $dimension
     * @param string           $operator
     * @param string|int|float $value
     */
    public function testFilter(
        string $dimension,
        string $operator,
        string|int|float $value
    ): void {
        $filter = new RangeFilter($dimension, $operator, $value, null);

        if (is_int($value)) {
            $valueType = DataType::LONG;
        } elseif (is_float($value)) {
            $valueType = DataType::DOUBLE;
        } else {
            $valueType = DataType::STRING;
        }
        $expected = [
            'type'           => 'range',
            'column'         => $dimension,
            'matchValueType' => $valueType->value,
        ];
        switch ($operator) {
            case '>=':
                $expected['lower']     = $value;
                $expected['lowerOpen'] = false;
                break;
            case '>':
                $expected['lower']     = $value;
                $expected['lowerOpen'] = true;
                break;
            case '<=':
                $expected['upper']     = $value;
                $expected['upperOpen'] = false;
                break;
            case '<':
                $expected['upper']     = $value;
                $expected['upperOpen'] = true;
                break;
        }

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testInvalidOperator(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('"is" is not a valid backing value for enum Level23\Druid\Types\BoundOperator');

        new RangeFilter('age', 'is', '18');
    }
}
