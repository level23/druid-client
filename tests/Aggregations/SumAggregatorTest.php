<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Aggregations;

use tests\TestCase;
use InvalidArgumentException;
use Level23\Druid\Types\DataType;
use Level23\Druid\Aggregations\SumAggregator;

class SumAggregatorTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            [DataType::LONG],
            [DataType::DOUBLE],
            [DataType::FLOAT],
            [DataType::STRING, true],
            ["asDF", true],
            ["LONG"],
        ];
    }

    /**
     * @dataProvider  dataProvider
     *
     * @param string $type
     * @param bool            $expectException
     */
    public function testAggregator(string $type, bool $expectException = false)
    {
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $aggregator = new SumAggregator('abc', 'dim123', $type);

        $this->assertEquals([
            'type'      => strtolower((string)$type) . 'Sum',
            'name'      => 'dim123',
            'fieldName' => 'abc',
        ], $aggregator->toArray());
    }
}