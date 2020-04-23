<?php

declare(strict_types=1);

namespace Level23\Druid\Tests\Collections;

use Mockery;
use DateTime;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Interval\IntervalInterface;
use Level23\Druid\Collections\IntervalCollection;

class IntervalCollectionTest extends TestCase
{
    public function testGetType()
    {
        $collection = new IntervalCollection();
        $this->assertEquals(IntervalInterface::class, $collection->getType());
    }

    public function testToArray()
    {
        $response = '2012-01-01T00:00:00.000/2012-01-03T00:00:00.000';
        $item     = Mockery::mock(Interval::class, [new DateTime('now - 1  hour'), new DateTime()]);
        $item->shouldReceive('getInterval')
            ->once()
            ->andReturn($response);

        $collection = new IntervalCollection($item);
        $this->assertEquals([$response], $collection->toArray());
    }
}
