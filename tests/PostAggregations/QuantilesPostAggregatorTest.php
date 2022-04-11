<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\PostAggregations;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\PostAggregations\QuantilesPostAggregator;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;

class QuantilesPostAggregatorTest extends TestCase
{
    public function testQuantiles(): void
    {
        $fieldAccess = new FieldAccessPostAggregator('field', 'field');
        $aggregator  = new QuantilesPostAggregator(
            $fieldAccess,
            'percentile',
            [0.95, 1]
        );

        $this->assertEquals([
            'type'     => 'quantilesDoublesSketchToQuantiles',
            'name'     => 'percentile',
            'field'    => $fieldAccess->toArray(),
            'fractions' =>  [0.95, 1],
        ], $aggregator->toArray());
    }
}
