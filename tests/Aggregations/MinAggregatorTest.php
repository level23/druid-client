<?php

namespace tests\Level23\Druid\Aggregations;

use Level23\Druid\Aggregations\MinAggregator;
use Level23\Druid\Types\DataType;
use tests\TestCase;

class MinAggregatorTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            [DataType::LONG()],
            [DataType::DOUBLE()],
            [DataType::FLOAT()],
            [DataType::STRING(), true],
        ];
    }

    /**
     * @dataProvider  dataProvider
     *
     * @param DataType $type
     * @param bool     $expectException
     */
    public function testAggregator($type, bool $expectException = false)
    {
        if ($expectException) {
            $this->expectException(\InvalidArgumentException::class);
        }

        $aggregator = new MinAggregator('abc', 'dim123', $type);
        $this->assertEquals([
            'type'      => $type . 'Min',
            'name'      => 'dim123',
            'fieldName' => 'abc',
        ], $aggregator->getAggregator());

        $this->assertEquals('dim123', $aggregator->getOutputName());
    }
}