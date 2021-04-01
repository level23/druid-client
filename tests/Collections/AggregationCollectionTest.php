<?php

declare(strict_types=1);

namespace Level23\Druid\Tests\Collections;

use Mockery;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Aggregations\SumAggregator;
use Level23\Druid\Aggregations\AggregatorInterface;
use Level23\Druid\Collections\AggregationCollection;

class AggregationCollectionTest extends TestCase
{
    public function testGetType(): void
    {
        $collection = new AggregationCollection();
        $this->assertEquals(AggregatorInterface::class, $collection->getType());
    }

    public function testToArray(): void
    {
        $response = [
            'type'      => 'longSum',
            'name'      => 'items',
            'fieldName' => 'items',
        ];
        $item     = Mockery::mock(SumAggregator::class, ['items']);
        $item->shouldReceive('toArray')
            ->once()
            ->andReturn($response);

        $collection = new AggregationCollection($item);
        $this->assertEquals([$response], $collection->toArray());
    }
}
