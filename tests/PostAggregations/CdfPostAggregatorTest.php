<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\PostAggregations;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\PostAggregations\CdfPostAggregator;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;

class CdfPostAggregatorTest extends TestCase
{
    public function testAggregator(): void
    {
        $fieldAccess = new FieldAccessPostAggregator('field', 'field');
        $aggregator  = new CdfPostAggregator(
            $fieldAccess,
            'cdfData',
            [1, 2, 3, 4, 5]
        );

        $this->assertEquals([
            'type'        => 'quantilesDoublesSketchToCDF',
            'name'        => 'cdfData',
            'field'       => $fieldAccess->toArray(),
            'splitPoints' => [1, 2, 3, 4, 5],
        ], $aggregator->toArray());
    }
}
