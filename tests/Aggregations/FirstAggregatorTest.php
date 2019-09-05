<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Aggregations;

use tests\TestCase;
use InvalidArgumentException;
use Level23\Druid\Types\DataType;
use Level23\Druid\Aggregations\FirstAggregator;

class FirstAggregatorTest extends TestCase
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
    public function testAggregator($type, bool $expectException = false)
    {
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $aggregator = new FirstAggregator('abc', 'dim123', $type);

        $this->assertEquals([
            'type'      => $type . 'First',
            'name'      => 'dim123',
            'fieldName' => 'abc',
        ], $aggregator->toArray());
    }
}