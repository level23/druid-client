<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\OrderBy;

use ValueError;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\OrderBy\OrderBy;
use Level23\Druid\Types\SortingOrder;
use Level23\Druid\Types\OrderByDirection;

class OrderByTest extends TestCase
{
    /**
     * @return array<array<string|bool|OrderByDirection|SortingOrder>>
     */
    public static function dataProvider(): array
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
     * @param string|OrderByDirection $direction
     * @param string|SortingOrder $sorting
     * @param bool   $expectException
     */
    public function testOrderBy(
        string $dimension,
        string|OrderByDirection $direction,
        string|SortingOrder $sorting,
        bool $expectException = false
    ): void {
        if ($expectException) {
            $this->expectException(ValueError::class);
        }

        $orderBy = new OrderBy($dimension, $direction, $sorting);

        $expectedDirection = ($direction == 'desc' || $direction == 'descending' || $direction == OrderByDirection::DESC)
            ? OrderByDirection::DESC
            : OrderByDirection::ASC;

        $this->assertEquals($orderBy->getDimension(), $dimension);
        $this->assertEquals($orderBy->getDirection(), $expectedDirection);

        $this->assertEquals([
            'dimension'      => $dimension,
            'direction'      => $expectedDirection->value,
            'dimensionOrder' => (is_string($sorting) ? SortingOrder::from($sorting) : $sorting)->value,
        ], $orderBy->toArray());
    }

    public function testDefaults(): void
    {
        $orderBy = new OrderBy('name');

        $this->assertEquals([
            'dimension'      => 'name',
            'direction'      => 'ascending',
            'dimensionOrder' => 'lexicographic',
        ], $orderBy->toArray());
    }
}
