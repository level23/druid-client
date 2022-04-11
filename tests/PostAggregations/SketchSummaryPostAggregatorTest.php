<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\PostAggregations;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;
use Level23\Druid\PostAggregations\SketchSummaryPostAggregator;

class SketchSummaryPostAggregatorTest extends TestCase
{
    public function testAggregator(): void
    {
        $fieldAccess = new FieldAccessPostAggregator('field', 'field');
        $aggregator  = new SketchSummaryPostAggregator($fieldAccess, 'debug');

        $this->assertEquals([
            'type'  => 'quantilesDoublesSketchToString',
            'name'  => 'debug',
            'field' => $fieldAccess->toArray(),
        ], $aggregator->toArray());
    }
}
