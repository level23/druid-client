<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Dimensions;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Dimensions\ListFilteredDimension;

class ListFilteredDimensionTest extends TestCase
{
    /**
     * @testWith [true]
     *           [false]
     *
     * @param bool $isWhitelist
     *
     * @return void
     */
    public function testDimension(bool $isWhitelist): void
    {
        $dim = new Dimension('foo', 'bar');

        $dimension = new ListFilteredDimension($dim, ['a', 'b', 'c'], $isWhitelist);

        $expected = [
            'type'        => 'listFiltered',
            'delegate'    => $dim->toArray(),
            'values'      => ['a', 'b', 'c'],
            'isWhitelist' => $isWhitelist,
        ];

        $this->assertEquals(
            $expected,
            $dimension->toArray()
        );

        $this->assertEquals('foo', $dimension->getDimension());
        $this->assertEquals('bar', $dimension->getOutputName());
    }
}
