<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use ValueError;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\DataType;
use Level23\Druid\Filters\BetweenFilter;

class BetweenFilterTest extends TestCase
{
    /**
     * @param int|float|string $minValue
     * @param int|float|string $maxValue
     * @param string|null      $dataType
     * @param bool             $expectException
     *
     * @testWith [12, 14]
     *           [12, 14, "long"]
     *           [12.6, 14.2, "long"]
     *           [12.6, 14.2]
     *           [12.6, 14.2, "string"]
     *           ["john", "doe"]
     *           ["john", "doe", "string"]
     *           ["john", "doe", "something", true]
     */
    public function testFilter(
        int|float|string $minValue,
        int|float|string $maxValue,
        ?string $dataType = null,
        bool $expectException = false
    ): void {
        if ($expectException) {
            $this->expectException(ValueError::class);
        }

        if (is_string($dataType)) {
            $dataType = DataType::from($dataType);
        }

        $filter = new BetweenFilter('age', $minValue, $maxValue, $dataType);

        if (is_null($dataType)) {
            if (is_int($minValue)) {
                $dataType = DataType::LONG;
            } elseif (is_float($minValue)) {
                $dataType = DataType::DOUBLE;
            } else {
                $dataType = DataType::STRING;
            }
        }

        $expected = [
            'type'           => 'range',
            'column'         => 'age',
            'matchValueType' => $dataType->value,
            'lower'          => $minValue,
            'lowerOpen'      => false,
            'upper'          => $maxValue,
            'upperOpen'      => true,
        ];

        $this->assertEquals($expected, $filter->toArray());
    }
}
