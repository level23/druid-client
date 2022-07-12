<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Dimensions;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Dimensions\PrefixFilteredDimension;

class PrefixFilteredDimensionTest extends TestCase
{
    public function testDimension(): void
    {
        $dim = new Dimension('foo', 'bar');

        $dimension = new PrefixFilteredDimension($dim, 'my');

        $expected = [
            'type'     => 'prefixFiltered',
            'delegate' => $dim->toArray(),
            'prefix'   => 'my',
        ];

        $this->assertEquals(
            $expected,
            $dimension->toArray()
        );

        $this->assertEquals('foo', $dimension->getDimension());
        $this->assertEquals('bar', $dimension->getOutputName());
    }
}
