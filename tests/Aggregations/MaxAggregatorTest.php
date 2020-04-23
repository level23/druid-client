<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Aggregations;

use InvalidArgumentException;
use tests\Level23\Druid\TestCase;
use Level23\Druid\Types\DataType;
use Level23\Druid\Aggregations\MaxAggregator;

class MaxAggregatorTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            [DataType::LONG],
            [DataType::DOUBLE],
            [DataType::FLOAT],
            [DataType::STRING, true],
        ];
    }

    /**
     * @dataProvider  dataProvider
     *
     * @param string $type
     * @param bool   $expectException
     */
    public function testAggregator(string $type, bool $expectException = false)
    {
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $aggregator = new MaxAggregator('abc', 'dim123', $type);

        $this->assertEquals([
            'type'      => $type . 'Max',
            'name'      => 'dim123',
            'fieldName' => 'abc',
        ], $aggregator->toArray());
    }
}
