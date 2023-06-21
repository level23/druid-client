<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Aggregations;

use InvalidArgumentException;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\DataType;
use Level23\Druid\Aggregations\MinAggregator;

class MinAggregatorTest extends TestCase
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
        ];
    }

    /**
     * @dataProvider  dataProvider
     *
     * @param string|DataType $type
     * @param bool   $expectException
     */
    public function testAggregator(string|DataType $type, bool $expectException = false): void
    {
        $strType = is_string($type) ? $type : $type->value;
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('Incorrect type given: ' . $strType . '. This can either be "long", "float" or "double"');
        }

        $aggregator = new MinAggregator('abc', 'dim123', $type);

        $this->assertEquals([
            'type'      => $strType . 'Min',
            'name'      => 'dim123',
            'fieldName' => 'abc',
        ], $aggregator->toArray());
    }
}
