<?php

declare(strict_types=1);

namespace tests\Level23\Druid\Collections;

use Mockery;
use tests\TestCase;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Dimensions\DimensionInterface;
use Level23\Druid\Transforms\TransformInterface;
use Level23\Druid\Transforms\ExpressionTransform;
use Level23\Druid\Collections\DimensionCollection;
use Level23\Druid\Collections\TransformCollection;

class TransformCollectionTest extends TestCase
{
    public function testGetType()
    {
        $collection = new TransformCollection();
        $this->assertEquals(TransformInterface::class, $collection->getType());
    }

    public function testToArray()
    {
        $response = [
            'type'       => 'expression',
            'name'       => 'fooBar',
            'expression' => 'concat(foo, bar)',
        ];
        $item     = Mockery::mock(ExpressionTransform::class, ['concat(foo, bar)', 'fooBar']);
        $item->shouldReceive('toArray')
            ->once()
            ->andReturn($response);

        $collection = new TransformCollection($item);
        $this->assertEquals([$response], $collection->toArray());
    }
}
