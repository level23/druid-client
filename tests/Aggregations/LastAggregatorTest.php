<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Aggregations;

use InvalidArgumentException;
use Level23\Druid\Tests\TestCase;
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
    public function testAggregator(string $type, bool $expectException = false): void
    {
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('Incorrect type given: ' . $type . '. This can either be "long", "float" or "double"');
        }

        $aggregator = new LastAggregator('abc', 'dim123', $type);

        $this->assertEquals([
            'type'      => $type . 'Last',
            'name'      => 'dim123',
            'fieldName' => 'abc',
        ], $aggregator->toArray());
    }
}
