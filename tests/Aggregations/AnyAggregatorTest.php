<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Aggregations;

use ValueError;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\DataType;
use Level23\Druid\Aggregations\AnyAggregator;

class AnyAggregatorTest extends TestCase
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
            ["wrong", true],
        ];
    }

    /**
     * @dataProvider  dataProvider
     *
     * @param DataType|string $type
     * @param bool            $expectException
     */
    public function testAggregator(DataType|string $type, bool $expectException = false): void
    {
        $strVal = (is_string($type) ? $type : $type->value);
        if ($expectException) {
            $this->expectException(ValueError::class);
            $this->expectExceptionMessage('"' . $strVal . '" is not a valid backing value for enum Level23\Druid\Types\DataType');
        }

        $aggregator = new AnyAggregator('abc', 'dim123', $type);

        $this->assertEquals([
            'type'      => $strVal . 'Any',
            'name'      => 'dim123',
            'fieldName' => 'abc',
        ], $aggregator->toArray());
    }

    public function testMaxStringBytes(): void
    {
        $aggregator = new AnyAggregator('revenue', 'totals', 'string', 2048);

        $this->assertEquals([
            'type'           => 'stringAny',
            'fieldName'      => 'revenue',
            'name'           => 'totals',
            'maxStringBytes' => 2048,
        ], $aggregator->toArray());
    }
}
