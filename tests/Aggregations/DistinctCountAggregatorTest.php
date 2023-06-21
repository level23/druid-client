<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Aggregations;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Aggregations\DistinctCountAggregator;

class DistinctCountAggregatorTest extends TestCase
{
    /**
     * @return array<array<string|int|null>>
     */
    public static function dataProvider(): array
    {
        return [
            ['dimension', 'abc', 32768],
            ['dimension', 'abc', null],
        ];
    }

    /**
     * @param string   $outputName
     * @param string   $dimension
     * @param int|null $size
     *
     * @dataProvider dataProvider
     */
    public function testAggregator(string $dimension, string $outputName, int $size = null): void
    {
        if ($size) {
            $aggregator = new DistinctCountAggregator($dimension, $outputName, $size);
        } else {
            $aggregator = new DistinctCountAggregator($dimension, $outputName);
        }

        $this->assertEquals([
            'type'               => 'thetaSketch',
            'fieldName'          => $dimension,
            'name'               => $outputName,
            'isInputThetaSketch' => false,
            'size'               => ($size ?: 16384),
        ], $aggregator->toArray());
    }
}
