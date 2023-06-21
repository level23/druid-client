<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Aggregations;

use ValueError;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\DataType;
use Level23\Druid\Aggregations\FirstAggregator;

class FirstAggregatorTest extends TestCase
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
            [DataType::STRING],
            ["object", true],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string|DataType $type
     * @param bool            $expectException
     */
    public function testAggregator(DataType|string $type, bool $expectException = false): void
    {
        $strVal = is_string($type) ? $type : $type->value;
        if ($expectException) {
            $this->expectException(ValueError::class);
            $this->expectExceptionMessage('"object" is not a valid backing value for enum Level23\Druid\Types\DataType');
        }

        $aggregator = new FirstAggregator('abc', 'dim123', $type);

        $this->assertEquals([
            'type'      => $strVal . 'First',
            'name'      => 'dim123',
            'fieldName' => 'abc',
        ], $aggregator->toArray());
    }
}
