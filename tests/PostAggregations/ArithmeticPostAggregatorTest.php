<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\PostAggregations;

use ValueError;
use Level23\Druid\Tests\TestCase;
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
    public function testAggregator(bool $floatingPointOrdering): void
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

    public function testAggregatorDefaults(): void
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

    public function testInvalidArithmeticFunction(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('"divide" is not a valid backing value for enum Level23\Druid\Types\ArithmeticFunction');

        $collections = new PostAggregationCollection(
            new FieldAccessPostAggregator('totals', 'totals'),
            new FieldAccessPostAggregator('rows', 'rows')
        );

        new ArithmeticPostAggregator(
            'average',
            'divide',
            $collections
        );
    }
}
