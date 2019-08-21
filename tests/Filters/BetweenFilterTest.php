<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use tests\TestCase;
use Level23\Druid\Types\SortingOrder;
use Level23\Druid\Filters\BetweenFilter;
use Level23\Druid\Extractions\LookupExtraction;

class BetweenFilterTest extends TestCase
{
    /**
     * @param string|int  $minValue
     * @param string|int  $maxValue
     * @param string|null $ordering
     * @param bool        $expectException
     *
     * @testWith [12, 14]
     *           ["john", "doe"]
     *           ["john", "doe", "alphanumeric"]
     *           ["john", "doe", "something", true]
     */
    public function testFilter($minValue, $maxValue, $ordering = null, bool $expectException = false)
    {
        if ($expectException) {
            $this->expectException(\InvalidArgumentException::class);
        }
        $filter = new BetweenFilter('age', $minValue, $maxValue, $ordering);

        if (is_numeric($minValue) && is_numeric($maxValue) && is_null($ordering)) {
            $ordering = 'numeric';
        } elseif (is_null($ordering)) {
            $ordering = SortingOrder::LEXICOGRAPHIC();
        }

        $expected = [
            'type'        => 'bound',
            'dimension'   => 'age',
            'ordering'    => (string)$ordering,
            'lower'       => (string)$minValue,
            'lowerStrict' => false,
            'upper'       => (string)$maxValue,
            'upperStrict' => false,
        ];

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testExtractionFunction()
    {
        $extractionFunction = new LookupExtraction(
            'real_age', false
        );

        $filter = new BetweenFilter('age', 12, 18, null, $extractionFunction);

        $expected = [
            'type'         => 'bound',
            'dimension'    => 'age',
            'ordering'     => 'numeric',
            'lower'        => '12',
            'lowerStrict'  => false,
            'upper'        => '18',
            'upperStrict'  => false,
            'extractionFn' => $extractionFunction->toArray(),
        ];

        $this->assertEquals($expected, $filter->toArray());
    }
}