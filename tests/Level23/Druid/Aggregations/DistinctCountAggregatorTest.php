<?php

namespace tests\Level23\Druid\Aggregations;

use Level23\Druid\Aggregations\DistinctCountAggregator;
use tests\TestCase;

class DistinctCountAggregatorTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            ['dimension', 'abc', 32768],
            ['dimension', 'abc', null],
        ];
    }

    /**
     * @param string   $outputName
     * @param string   $dimension
     * @param int|null $size
     *
     * @dataProvider dataProvider
     */
    public function testAggregator(string $dimension, string $outputName, int $size = null)
    {
        if ($size) {
            $aggregator = new DistinctCountAggregator($outputName, $dimension, $size);
        } else {
            $aggregator = new DistinctCountAggregator($outputName, $dimension);
        }

        $this->assertEquals([
            'type'               => 'thetaSketch',
            'fieldName'          => $outputName,
            'name'               => $dimension,
            'isInputThetaSketch' => false,
            'size'               => ($size ?: 16384),
        ], $aggregator->getAggregator());
    }
}