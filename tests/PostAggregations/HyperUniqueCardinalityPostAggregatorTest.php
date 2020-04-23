<?php
declare(strict_types=1);

namespace tests\Level23\Druid\PostAggregations;

use tests\Level23\Druid\TestCase;
use Level23\Druid\PostAggregations\HyperUniqueCardinalityPostAggregator;

class HyperUniqueCardinalityPostAggregatorTest extends TestCase
{
    public function testAggregator()
    {
        $aggregator = new HyperUniqueCardinalityPostAggregator(
            'myHyperUniqueField',
            'myOutputName'
        );

        $this->assertEquals([
            'type'      => 'hyperUniqueCardinality',
            'name'      => 'myOutputName',
            'fieldName' => 'myHyperUniqueField',
        ], $aggregator->toArray());
    }

    public function testAggregatorWithoutName()
    {
        $aggregator = new HyperUniqueCardinalityPostAggregator('myHyperUniqueField');

        $this->assertEquals([
            'type'      => 'hyperUniqueCardinality',
            'fieldName' => 'myHyperUniqueField',
        ], $aggregator->toArray());
    }
}
