<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Aggregations;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Collections\DimensionCollection;
use Level23\Druid\Aggregations\CardinalityAggregator;

class CardinalityAggregatorTest extends TestCase
{
    /**
     * @testWith [true, true]
     *           [false, true]
     *           [false, false]
     *
     * @param bool $byRow
     * @param bool $round
     */
    public function testAggregation(bool $byRow, bool $round)
    {
        $dimensions = new DimensionCollection(
            new Dimension('dim1'),
            new Dimension('dim2')
        );
        $aggregator = new CardinalityAggregator(
            'myCardinality',
            $dimensions,
            $byRow,
            $round
        );

        $this->assertEquals([
            'type'   => 'cardinality',
            'name'   => 'myCardinality',
            'fields' => $dimensions->toArray(),
            'byRow'  => $byRow,
            'round'  => $round,
        ], $aggregator->toArray());
    }

    public function testDefaults()
    {
        $dimensions = new DimensionCollection(
            new Dimension('dim1'),
            new Dimension('dim2')
        );
        $aggregator = new CardinalityAggregator(
            'myCardinality',
            $dimensions
        );

        $this->assertEquals([
            'type'   => 'cardinality',
            'name'   => 'myCardinality',
            'fields' => $dimensions->toArray(),
            'byRow'  => false,
            'round'  => false,
        ], $aggregator->toArray());
    }
}
