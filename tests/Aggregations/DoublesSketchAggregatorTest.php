<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Aggregations;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Aggregations\DoublesSketchAggregator;

class DoublesSketchAggregatorTest extends TestCase
{
    /**
     * @testWith [256, null]
     *           [null, null]
     *           [null, 10000000]
     *           [256, 10000000]
     *
     *
     * @param int|null $sizeAndAccuracy
     * @param int|null $maxStreamLength
     *
     * @return void
     */
    public function testAggregator(?int $sizeAndAccuracy, ?int $maxStreamLength): void
    {
        $aggregator = new DoublesSketchAggregator(
            'salary',
            'salaryData',
            $sizeAndAccuracy,
            $maxStreamLength
        );

        $result = [
            'type'      => 'quantilesDoublesSketch',
            'name'      => 'salaryData',
            'fieldName' => 'salary',
        ];

        if ($sizeAndAccuracy) {
            $result['k'] = $sizeAndAccuracy;
        }

        if ($maxStreamLength) {
            $result['maxStreamLength'] = $maxStreamLength;
        }

        $this->assertEquals($result, $aggregator->toArray());
    }
}
