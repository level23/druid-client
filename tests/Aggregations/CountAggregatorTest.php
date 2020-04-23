<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Aggregations;

use Level23\Druid\Tests\TestCase;
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
