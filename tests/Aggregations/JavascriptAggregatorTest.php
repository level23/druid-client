<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Aggregations;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Aggregations\JavascriptAggregator;

class JavascriptAggregatorTest extends TestCase
{
    public function testAggregator()
    {
        $fnAggregate = "function(current, a, b)      { return current + (Math.log(a) * b); }";
        $fnCombine   = "function(partialA, partialB) { return partialA + partialB; }";
        $fnReset     = "function()                   { return 10; }";

        $aggregator = new JavascriptAggregator(
            ['dim123', 'names'],
            'total',
            $fnAggregate,
            $fnCombine,
            $fnReset
        );

        $this->assertEquals([
            'type'        => 'javascript',
            'name'        => 'total',
            'fieldNames'  => ['dim123', 'names'],
            'fnAggregate' => $fnAggregate,
            'fnCombine'   => $fnCombine,
            'fnReset'     => $fnReset,
        ], $aggregator->toArray());

        $this->assertEquals('total', $aggregator->getOutputName());
    }
}
