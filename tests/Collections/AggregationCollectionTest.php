<?php

declare(strict_types=1);

namespace tests\Level23\Druid\Collections;

use Level23\Druid\Aggregations\AggregatorInterface;
use Level23\Druid\Aggregations\SumAggregator;
use Level23\Druid\Collections\AggregationCollection;
use Mockery;
use tests\TestCase;

class AggregationCollectionTest extends TestCase
{
    public function testGetType()
    {
        $collection = new AggregationCollection();
        $this->assertEquals(AggregatorInterface::class, $collection->getType());
    }

    public function testToArray()
    {
        $response = [
            'type'      => 'longSum',
            'name'      => 'items',
            'fieldName' => 'items',
        ];
        $item     = Mockery::mock(SumAggregator::class, ['items']);
        $item->shouldReceive('getAggregator')
            ->once()
            ->andReturn($response);

        $collection = new AggregationCollection($item);
        $this->assertEquals([$response], $collection->toArray());
    }
}
