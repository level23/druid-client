<?php

namespace tests\Level23\Druid\Aggregations;

use Level23\Druid\Aggregations\DistinctCountAggregator;
use tests\TestCase;

class DistinctCountAggregatorTest extends TestCase
{
    public function testAggregator()
    {
        $aggregator = new DistinctCountAggregator('abc', 'dim123', 32768);
        $this->assertEquals([
            'type'               => 'thetaSketch',
            'name'               => 'abc',
            'fieldName'          => 'dim123',
            'isInputThetaSketch' => false,
            'size'               => 32768,
        ], $aggregator->getAggregator());
    }
}