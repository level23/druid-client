<?php

declare(strict_types=1);

namespace tests\Level23\Druid\Collections;

use Level23\Druid\Collections\PostAggregationCollection;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;
use Level23\Druid\PostAggregations\PostAggregatorInterface;
use Mockery;
use tests\TestCase;

class PostAggregationCollectionTest extends TestCase
{
    public function testGetType()
    {
        $collection = new PostAggregationCollection();
        $this->assertEquals(PostAggregatorInterface::class, $collection->getType());
    }

    public function testToArray()
    {
        $response = [
            'type'      => 'fieldAccess',
            'name'      => 'newAge',
            'fieldName' => 'age',
        ];
        $item     = Mockery::mock(FieldAccessPostAggregator::class, ['age', 'newAge']);
        $item->shouldReceive('toArray')
            ->once()
            ->andReturn($response);

        $collection = new PostAggregationCollection($item);
        $this->assertEquals([$response], $collection->toArray());
    }
}
