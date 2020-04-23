<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\PostAggregations;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\PostAggregations\LeastPostAggregator;
use Level23\Druid\Collections\PostAggregationCollection;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;

class LeastPostAggregatorTest extends TestCase
{
    /**
     * @testWith ["long"]
     *           ["double"]
     *
     * @param string $type
     */
    public function testAggregator(string $type)
    {
        $collections = new PostAggregationCollection(
            new FieldAccessPostAggregator('field1', 'field1'),
            new FieldAccessPostAggregator('field2', 'field2')
        );

        $aggregator = new LeastPostAggregator('leastValue', $collections, $type);

        $this->assertEquals([
            'type'   => $type . 'Least',
            'name'   => 'leastValue',
            'fields' => $collections->toArray(),
        ], $aggregator->toArray());
    }
}
