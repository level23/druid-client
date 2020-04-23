<?php
declare(strict_types=1);

namespace tests\Level23\Druid\PostAggregations;

use tests\Level23\Druid\TestCase;
use Level23\Druid\PostAggregations\ConstantPostAggregator;

class ConstantPostAggregatorTest extends TestCase
{
    public function testAggregator()
    {
        $aggregator = new ConstantPostAggregator('pi', 3.14);

        $this->assertEquals([
            'type'  => 'constant',
            'name'  => 'pi',
            'value' => 3.14,
        ], $aggregator->toArray());
    }
}
