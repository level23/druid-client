<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Aggregations;

use tests\TestCase;
use Level23\Druid\Aggregations\CountAggregator;

class CountAggregatorTest extends TestCase
{
    public function testAggregator()
    {
        $name = 'numberOfThings';

        $aggregator = new CountAggregator($name);

        $this->assertEquals([
            'type' => 'count',
            'name' => $name,
        ], $aggregator->toArray());
    }
}