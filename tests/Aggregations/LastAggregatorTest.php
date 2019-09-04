<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Aggregations;

use tests\TestCase;
use InvalidArgumentException;
use Level23\Druid\Types\DataType;
use Level23\Druid\Aggregations\LastAggregator;

class LastAggregatorTest extends TestCase
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

        $aggregator = new LastAggregator('abc', 'dim123', $type);

        $this->assertEquals([
            'type'      => $type . 'Last',
            'name'      => 'dim123',
            'fieldName' => 'abc',
        ], $aggregator->toArray());
    }
}