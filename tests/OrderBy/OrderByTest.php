<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\OrderBy;

use InvalidArgumentException;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\OrderBy\OrderBy;
use Level23\Druid\Types\SortingOrder;
use Level23\Druid\Types\OrderByDirection;

class OrderByTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            ['name', OrderByDirection::DESC, SortingOrder::NUMERIC],
            ['name', OrderByDirection::ASC, SortingOrder::ALPHANUMERIC],
            ['name', OrderByDirection::ASC, 'numeric'],
            ['name', OrderByDirection::ASC, 'wrong', true],
            ['name', 'desc', 'strlen'],
            ['name', 'descending', 'strlen'],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $dimension
     * @param string $direction
     * @param string $sorting
     * @param bool   $expectException
     */
    public function testOrderBy(string $dimension, string $direction, string $sorting, bool $expectException = false)
    {
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $orderBy = new OrderBy($dimension, $direction, $sorting);

        $expectedDirection = ($direction == 'desc' || $direction == 'descending')
            ? OrderByDirection::DESC
            : OrderByDirection::ASC;

        $this->assertEquals($orderBy->getDimension(), $dimension);
        $this->assertEquals($orderBy->getDirection(), $expectedDirection);

        $this->assertEquals([
            'dimension'      => $dimension,
            'direction'      => $expectedDirection,
            'dimensionOrder' => $sorting,
        ], $orderBy->toArray());
    }

    public function testDefaults()
    {
        $orderBy = new OrderBy('name');

        $this->assertEquals([
            'dimension'      => 'name',
            'direction'      => 'ascending',
            'dimensionOrder' => 'lexicographic',
        ], $orderBy->toArray());
    }
}
