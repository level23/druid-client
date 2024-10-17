<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Lookups;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Lookups\MapLookup;

class MapLookupTest extends TestCase
{
    public function testLookup(): void
    {
        $lookup = new MapLookup(['foo' => 'Foo', 'bar' => 'Baz']);

        $this->assertEquals(
            [
                'type' => 'map',
                'map'  => ['foo' => 'Foo', 'bar' => 'Baz'],
            ],
            $lookup->toArray()
        );
    }
}
