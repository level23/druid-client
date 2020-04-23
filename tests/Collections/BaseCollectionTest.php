<?php

declare(strict_types=1);

namespace tests\Level23\Druid\Collections;

use ArrayIterator;
use InvalidArgumentException;
use tests\Level23\Druid\TestCase;
use Level23\Druid\Aggregations\SumAggregator;
use Level23\Druid\Collections\AggregationCollection;

class BaseCollectionTest extends TestCase
{
    public function testGetType()
    {
        $collection = new AggregationCollection();
        $iterator   = $collection->getIterator();

        $this->assertInstanceOf(ArrayIterator::class, $iterator);
        $this->assertEquals([], $iterator->getArrayCopy());

        $item = new SumAggregator('items');

        $collection->add($item);

        $iterator = $collection->getIterator();
        $this->assertInstanceOf(ArrayIterator::class, $iterator);

        $this->assertEquals([$item], $iterator->getArrayCopy());
    }

    public function testAdd()
    {
        $collection = new AggregationCollection();

        $item = new SumAggregator('items');
        $collection->add($item);

        $this->assertEquals($collection->offsetGet(0), $item);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('We only accept instances of type');
        $collection->add('hallo');
    }

    public function testOffsetExists()
    {
        $collection = new AggregationCollection();

        $this->assertFalse($collection->offsetExists(0));

        $item = new SumAggregator('items');
        $collection->add($item);

        $this->assertTrue($collection->offsetExists(0));
    }

    public function testOffsetGet()
    {
        $collection = new AggregationCollection();

        $this->assertEquals(null, $collection->offsetGet(0));

        $item = new SumAggregator('items');
        $collection->add($item);

        $this->assertEquals($item, $collection->offsetGet(0));
    }

    public function testOffsetSet()
    {
        $collection = new AggregationCollection();

        $item = new SumAggregator('items');
        $collection->offsetSet(12, $item);

        $this->assertEquals($item, $collection->offsetGet(12));

        $collection->offsetSet(null, $item);
        $this->assertEquals($item, $collection->offsetGet(13));
    }

    public function testOffsetSetIncorrectType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('We only accept');

        $collection = new AggregationCollection();
        $collection->offsetSet(1, 'hallo');
    }

    public function testOffsetUnset()
    {
        $collection = new AggregationCollection();

        $item = new SumAggregator('items');
        $collection->offsetSet(12, $item);

        $this->assertEquals($item, $collection->offsetGet(12));

        $collection->offsetUnset(12);

        $this->assertNull($collection->offsetGet(12));
    }

    public function testCount()
    {
        $collection = new AggregationCollection();

        $this->assertEquals(0, $collection->count());

        $item = new SumAggregator('items');
        $collection->add($item);

        $this->assertEquals(1, $collection->count());

        $this->assertNull($collection->offsetGet(12));
    }
}
