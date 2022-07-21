<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Dimensions;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Dimensions\RegexFilteredDimension;

class RegexFilteredDimensionTest extends TestCase
{
    public function testDimension(): void
    {
        $dim = new Dimension('foo', 'bar');

        $dimension = new RegexFilteredDimension($dim, '^a$');

        $expected = [
            'type'     => 'regexFiltered',
            'delegate' => $dim->toArray(),
            'pattern'  => '^a$',
        ];

        $this->assertEquals(
            $expected,
            $dimension->toArray()
        );

        $this->assertEquals('foo', $dimension->getDimension());
        $this->assertEquals('bar', $dimension->getOutputName());
    }
}
