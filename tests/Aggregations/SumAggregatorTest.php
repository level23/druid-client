<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Aggregations;

use ValueError;
use InvalidArgumentException;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\DataType;
use Level23\Druid\Aggregations\SumAggregator;

class SumAggregatorTest extends TestCase
{
    /**
     * @return array<array<string|bool|DataType>>
     */
    public static function dataProvider(): array
    {
        return [
            [DataType::LONG],
            [DataType::DOUBLE],
            [DataType::FLOAT],
            [DataType::STRING, true],
            ["asDF", false, true],
            ["LONG"],
        ];
    }

    /**
     * @dataProvider  dataProvider
     *
     * @param string|DataType $type
     * @param bool            $expectException
     * @param bool            $exceptValueError
     */
    public function testAggregator(string|DataType $type, bool $expectException = false, bool $exceptValueError = false): void
    {
        $strType = is_string($type) ? strtolower($type) : $type->value;
        if( $exceptValueError) {
            $this->expectException(ValueError::class);
            $this->expectExceptionMessage('"'.$strType.'" is not a valid backing value for enum Level23\Druid\Types\DataType');
        }
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('Incorrect type given: ' . $strType . '. This can either be "long", "float" or "double"');
        }

        $aggregator = new SumAggregator('abc', 'dim123', $type);

        $this->assertEquals([
            'type'      => $strType . 'Sum',
            'name'      => 'dim123',
            'fieldName' => 'abc',
        ], $aggregator->toArray());
    }
}
