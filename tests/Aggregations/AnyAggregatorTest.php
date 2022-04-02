<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Aggregations;

use InvalidArgumentException;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\DataType;
use Level23\Druid\Aggregations\AnyAggregator;

class AnyAggregatorTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            [DataType::LONG],
            [DataType::DOUBLE],
            [DataType::FLOAT],
            [DataType::STRING],
            ["wrong", true],
        ];
    }

    /**
     * @dataProvider  dataProvider
     *
     * @param string $type
     * @param bool   $expectException
     */
    public function testAggregator(string $type, bool $expectException = false): void
    {
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $aggregator = new AnyAggregator('abc', 'dim123', $type);

        $this->assertEquals([
            'type'      => $type . 'Any',
            'name'      => 'dim123',
            'fieldName' => 'abc',
        ], $aggregator->toArray());
    }

    public function testMaxStringBytes(): void
    {
        $aggregator = new AnyAggregator('revenue', 'totals', 'string', 2048);

        $this->assertEquals([
            'type'           => 'stringAny',
            'fieldName'      => 'revenue',
            'name'           => 'totals',
            'maxStringBytes' => 2048,
        ], $aggregator->toArray());
    }
}
