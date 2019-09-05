<?php
declare(strict_types=1);

namespace tests\Level23\Druid\PostAggregations;

use tests\TestCase;
use Level23\Druid\PostAggregations\HyperUniqueCardinalityPostAggregator;

class HyperUniqueCardinalityPostAggregatorTest extends TestCase
{
    public function testAggregator()
    {
        $aggregator = new HyperUniqueCardinalityPostAggregator(
            'myHyperUniqueCardinality',
            'myHyperUniqueField'
        );

        $this->assertEquals([
            'type'      => 'hyperUniqueCardinality',
            'name'      => 'myHyperUniqueCardinality',
            'fieldName' => 'myHyperUniqueField',
        ], $aggregator->toArray());
    }
}
