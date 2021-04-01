<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\PostAggregations;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\PostAggregations\HyperUniqueCardinalityPostAggregator;

class HyperUniqueCardinalityPostAggregatorTest extends TestCase
{
    public function testAggregator(): void
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

    public function testAggregatorWithoutName(): void
    {
        $aggregator = new HyperUniqueCardinalityPostAggregator('myHyperUniqueField');

        $this->assertEquals([
            'type'      => 'hyperUniqueCardinality',
            'fieldName' => 'myHyperUniqueField',
        ], $aggregator->toArray());
    }
}
