<?php

namespace tests\Level23\Druid\Aggregations;

use InvalidArgumentException;
use Level23\Druid\Aggregations\SumAggregator;
use Level23\Druid\Types\DataType;
use tests\TestCase;

class SumAggregatorTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            [DataType::LONG()],
            [DataType::DOUBLE()],
            [DataType::FLOAT()],
            [DataType::STRING(), true],
            ["asDF", true],
            ["LONG"],
        ];
    }

    /**
     * @dataProvider  dataProvider
     *
     * @param DataType|string $type
     * @param bool            $expectException
     */
    public function testAggregator($type, bool $expectException = false)
    {
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $aggregator = new SumAggregator('abc', 'dim123', $type);

        $this->assertEquals([
            'type'      => strtolower($type) . 'Sum',
            'name'      => 'dim123',
            'fieldName' => 'abc',
        ], $aggregator->toArray());
    }
}