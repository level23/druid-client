<?php
declare(strict_types=1);

namespace tests\Level23\Druid\PostAggregations;

use tests\TestCase;
use InvalidArgumentException;
use Level23\Druid\Collections\PostAggregationCollection;
use Level23\Druid\PostAggregations\GreatestPostAggregator;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;

class GreatestPostAggregatorTest extends TestCase
{
    /**
     * @testWith ["long"]
     *           ["double"]
     *           ["StRinG"]
     *           ["DOUBLE"]
     *
     * @param $type
     */
    public function testAggregator($type)
    {
        $collections = new PostAggregationCollection(
            new FieldAccessPostAggregator('field1', 'field1'),
            new FieldAccessPostAggregator('field2', 'field2')
        );

        if (!in_array(strtolower($type), ['long', 'double'])) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('Supported types are "long" and "double".');

            new GreatestPostAggregator('greatestValue', $collections, $type);
        } else {
            $aggregator = new GreatestPostAggregator('greatestValue', $collections, $type);

            $this->assertEquals([
                'type'   => strtolower($type) . 'Greatest',
                'name'   => 'greatestValue',
                'fields' => $collections->toArray(),
            ], $aggregator->toArray());
        }
    }
}
