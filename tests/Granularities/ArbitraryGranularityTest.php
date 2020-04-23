<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Granularities;

use InvalidArgumentException;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Granularities\ArbitraryGranularity;

class ArbitraryGranularityTest extends TestCase
{
    /**
     * @testWith ["day", true]
     *           ["week", false]
     *           ["John", false, true]
     *
     * @param string $queryGranularity
     * @param bool   $rollup
     * @param bool   $expectException
     *
     * @throws \Exception
     */
    public function testGranularity(string $queryGranularity, bool $rollup, bool $expectException = false)
    {
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $intervalCollection = new IntervalCollection(
            new Interval('12-04-2019', '15-04-2019')
        );
        $granularity        = new ArbitraryGranularity(
            $queryGranularity,
            $rollup,
            $intervalCollection
        );

        $this->assertEquals([
            'type'             => 'arbitrary',
            'queryGranularity' => $queryGranularity,
            'rollup'           => $rollup,
            'intervals'        => $intervalCollection->toArray(),
        ], $granularity->toArray());
    }
}
