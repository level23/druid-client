<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\PostAggregations;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Collections\PostAggregationCollection;
use Level23\Druid\PostAggregations\JavaScriptPostAggregator;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;

class JavaScriptPostAggregatorTest extends TestCase
{
    public function testAggregator()
    {
        $collections = new PostAggregationCollection(
            new FieldAccessPostAggregator('delta', 'delta'),
            new FieldAccessPostAggregator('total', 'total')
        );

        $jsFunction = "function(delta, total) { return 100 * Math.abs(delta) / total; }";

        $aggregator = new JavaScriptPostAggregator('mySpecialField', $collections, $jsFunction);

        $this->assertEquals([
            'type'       => 'javascript',
            'name'       => 'mySpecialField',
            'fieldNames' => $collections->toArray(),
            'function'   => $jsFunction,
        ], $aggregator->toArray());
    }
}
