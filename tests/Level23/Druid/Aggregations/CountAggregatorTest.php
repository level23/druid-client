<?php

namespace tests\Level23\Druid\Aggregations;

use Level23\Druid\Aggregations\CountAggregator;
use tests\TestCase;

class CountAggregatorTest extends TestCase
{
    public function testCountAggregator()
    {
        $aggregator = new CountAggregator("numberOfThings");
        $this->assertEquals( [
            'type' => 'count',
            'name' => 'numberOfThings'
        ], $aggregator->getAggregator());
    }
}