<?php
declare(strict_types=1);

namespace tests\Level23\Druid\PostAggregations;

use tests\TestCase;
use Level23\Druid\Collections\PostAggregationCollection;
use Level23\Druid\PostAggregations\ArithmeticPostAggregator;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;

class ArithmeticPostAggregatorTest extends TestCase
{
    /**
     * @testWith [true]
     *           [false]
     *
     * @param bool $floatingPointOrdering
     */
    public function testAggregator(bool $floatingPointOrdering)
    {
        $collections = new PostAggregationCollection(
            new FieldAccessPostAggregator('totals', 'totals'),
            new FieldAccessPostAggregator('rows', 'rows')
        );
        $aggregator  = new ArithmeticPostAggregator(
            'average',
            '/',
            $collections,
            $floatingPointOrdering
        );

        $this->assertEquals([
            'type'     => 'arithmetic',
            'name'     => 'average',
            'fn'       => '/',
            'fields'   => $collections->toArray(),
            'ordering' => $floatingPointOrdering ? null : 'numericFirst',
        ], $aggregator->toArray()
        );
    }

    public function testAggregatorDefaults()
    {
        $collections = new PostAggregationCollection(
            new FieldAccessPostAggregator('totals', 'totals'),
            new FieldAccessPostAggregator('rows', 'rows')
        );
        $aggregator  = new ArithmeticPostAggregator(
            'average',
            '/',
            $collections
        );

        $this->assertEquals([
            'type'     => 'arithmetic',
            'name'     => 'average',
            'fn'       => '/',
            'fields'   => $collections->toArray(),
            'ordering' => null,
        ], $aggregator->toArray()
        );
    }
}