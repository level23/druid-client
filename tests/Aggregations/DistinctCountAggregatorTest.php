<?php

namespace tests\Level23\Druid\Aggregations;

use Level23\Druid\Aggregations\DistinctCountAggregator;
use tests\TestCase;

class DistinctCountAggregatorTest extends TestCase
{
    /**
     * @param string   $outputName
     * @param string   $dimension
     * @param int|null $size
     *
     * @testWith ['dimension', 'abc', 32768],
     *           ['dimension', 'abc', null],
     */
    public function testAggregator(string $dimension, string $outputName, int $size = null)
    {
        if ($size) {
            $aggregator = new DistinctCountAggregator($dimension, $outputName, $size);
        } else {
            $aggregator = new DistinctCountAggregator($dimension, $outputName);
        }

        $this->assertEquals([
            'type'               => 'thetaSketch',
            'fieldName'          => $dimension,
            'name'               => $outputName,
            'isInputThetaSketch' => false,
            'size'               => ($size ?: 16384),
        ], $aggregator->toArray());
    }
}