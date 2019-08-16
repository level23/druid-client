<?php

namespace tests\Level23\Druid\Aggregations;

use Level23\Druid\Aggregations\CountAggregator;
use tests\TestCase;

class CountAggregatorTest extends TestCase
{
    public function testAggregator()
    {
        $name = 'numberOfThings';

        $aggregator = new CountAggregator($name);

        $this->assertEquals( [
            'type' => 'count',
            'name' => $name
        ], $aggregator->toArray());
    }
}