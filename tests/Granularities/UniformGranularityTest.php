<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Granularities;

use InvalidArgumentException;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Granularities\UniformGranularity;

class UniformGranularityTest extends TestCase
{
    /**
     * @testWith ["day", "day", true]
     *           ["day", "week", false]
     *           ["day", "John", false, true]
     *           ["John", "year", false, true]
     *
     * @param string $segmentGranularity
     * @param string $queryGranularity
     * @param bool   $rollup
     * @param bool   $expectException
     *
     * @throws \Exception
     */
    public function testGranularity(
        string $segmentGranularity,
        string $queryGranularity,
        bool $rollup,
        bool $expectException = false
    ) {
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $intervalCollection = new IntervalCollection(
            new Interval('12-04-2019', '15-04-2019')
        );
        $granularity        = new UniformGranularity(
            $segmentGranularity,
            $queryGranularity,
            $rollup,
            $intervalCollection
        );

        $this->assertEquals([
            'type'               => 'uniform',
            'segmentGranularity' => $segmentGranularity,
            'queryGranularity'   => $queryGranularity,
            'rollup'             => $rollup,
            'intervals'          => $intervalCollection->toArray(),
        ], $granularity->toArray());
    }
}
