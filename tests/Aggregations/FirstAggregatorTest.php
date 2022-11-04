<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Aggregations;

use InvalidArgumentException;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\DataType;
use Level23\Druid\Aggregations\FirstAggregator;

class FirstAggregatorTest extends TestCase
{
    /**
     * @return array<array<string|bool>>
     */
    public function dataProvider(): array
    {
        return [
            [DataType::LONG],
            [DataType::DOUBLE],
            [DataType::FLOAT],
            [DataType::STRING],
            ["object", true],
        ];
    }

    /**
     * @dataProvider  dataProvider
     *
     * @param string $type
     * @param bool   $expectException
     */
    public function testAggregator($type, bool $expectException = false): void
    {
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('The given output type is invalid: object. Allowed are: string,float,long,double');
        }

        $aggregator = new FirstAggregator('abc', 'dim123', $type);

        $this->assertEquals([
            'type'      => $type . 'First',
            'name'      => 'dim123',
            'fieldName' => 'abc',
        ], $aggregator->toArray());
    }
}
