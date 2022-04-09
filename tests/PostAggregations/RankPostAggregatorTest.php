<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\PostAggregations;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\PostAggregations\RankPostAggregator;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;

class RankPostAggregatorTest extends TestCase
{
    public function testQuantile(): void
    {
        $fieldAccess = new FieldAccessPostAggregator('field', 'field');
        $aggregator  = new RankPostAggregator(
            $fieldAccess,
            'rank',
            0.95
        );

        $this->assertEquals([
            'type'     => 'quantilesDoublesSketchToRank',
            'name'     => 'rank',
            'field'    => $fieldAccess->toArray(),
            'value' => 0.95,
        ], $aggregator->toArray());
    }
}
