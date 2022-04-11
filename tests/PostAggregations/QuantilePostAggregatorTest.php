<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\PostAggregations;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\PostAggregations\QuantilePostAggregator;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;

class QuantilePostAggregatorTest extends TestCase
{
    public function testQuantile(): void
    {
        $fieldAccess = new FieldAccessPostAggregator('field', 'field');
        $aggregator  = new QuantilePostAggregator(
            $fieldAccess,
            'percentile',
            0.95
        );

        $this->assertEquals([
            'type'     => 'quantilesDoublesSketchToQuantile',
            'name'     => 'percentile',
            'field'    => $fieldAccess->toArray(),
            'fraction' => 0.95,
        ], $aggregator->toArray());
    }
}
