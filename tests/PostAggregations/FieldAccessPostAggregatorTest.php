<?php
declare(strict_types=1);

namespace tests\Level23\Druid\PostAggregations;

use tests\TestCase;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;

class FieldAccessPostAggregatorTest extends TestCase
{
    /**
     * @testWith [true]
     *           [false]
     *
     * @param bool $finalizing
     */
    public function testAggregator(bool $finalizing)
    {
        $aggregator = new FieldAccessPostAggregator('foo', 'bar', $finalizing);

        $this->assertEquals([
            'type'      => ($finalizing ? 'finalizingFieldAccess' : 'fieldAccess'),
            'name'      => 'bar',
            'fieldName' => 'foo',
        ], $aggregator->toArray());
    }
}
