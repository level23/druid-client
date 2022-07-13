<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\PostAggregations;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\PostAggregations\HistogramPostAggregator;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;

class HistogramPostAggregatorTest extends TestCase
{
    /**
     * @testWith [null, null]
     *           [null, 10]
     *           [[1,2,3], null]
     *
     * @param array<int>|null $splitPoints
     * @param int|null        $numBins
     */
    public function testHistogram(?array $splitPoints, ?int $numBins): void
    {
        $fieldAccess = new FieldAccessPostAggregator('field', 'field');
        $aggregator  = new HistogramPostAggregator(
            $fieldAccess,
            'histogram',
            $splitPoints,
            $numBins
        );

        $result = [
            'type'  => 'quantilesDoublesSketchToHistogram',
            'name'  => 'histogram',
            'field' => $fieldAccess->toArray(),
        ];

        if ($splitPoints) {
            $result['splitPoints'] = $splitPoints;
        }

        if ($numBins) {
            $result['numBins'] = $numBins;
        }

        $this->assertEquals($result, $aggregator->toArray());
    }
}
