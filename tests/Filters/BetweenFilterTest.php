<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use ValueError;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\SortingOrder;
use Level23\Druid\Filters\BetweenFilter;
use Level23\Druid\Extractions\LookupExtraction;

class BetweenFilterTest extends TestCase
{
    /**
     * @param int|string  $minValue
     * @param int|string  $maxValue
     * @param string|null $ordering
     * @param bool        $expectException
     *
     * @testWith [12, 14]
     *           ["john", "doe"]
     *           ["john", "doe", "alphanumeric"]
     *           ["john", "doe", "something", true]
     */
    public function testFilter(int|string $minValue, int|string $maxValue, string $ordering = null, bool $expectException = false): void
    {
        if ($expectException) {
            $this->expectException(ValueError::class);
        }
        $filter = new BetweenFilter('age', $minValue, $maxValue, $ordering);

        if (is_numeric($minValue) && is_numeric($maxValue) && is_null($ordering)) {
            $ordering = 'numeric';
        } elseif (is_null($ordering)) {
            $ordering = SortingOrder::LEXICOGRAPHIC;
        }

        $expected = [
            'type'        => 'bound',
            'dimension'   => 'age',
            'ordering'    => (is_string($ordering) ? SortingOrder::from($ordering) : $ordering)->value,
            'lower'       => (string)$minValue,
            'lowerStrict' => false,
            'upper'       => (string)$maxValue,
            'upperStrict' => true,
        ];

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testExtractionFunction(): void
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
            'upperStrict'  => true,
            'extractionFn' => $extractionFunction->toArray(),
        ];

        $this->assertEquals($expected, $filter->toArray());
    }

    /**
     * @testWith ["12", "15"]
     *           ["-inf", "15"]
     *           ["-inf", "+inf"]
     *           ["1", "+inf"]
     *
     * @param string $minValue
     * @param string $maxValue
     */
    public function testDefaultOrdering(string $minValue, string $maxValue): void
    {
        $filter = new BetweenFilter('age', $minValue, $maxValue);

        $expected = [
            'type'        => 'bound',
            'dimension'   => 'age',
            'ordering'    => is_numeric($minValue) && is_numeric($maxValue) ? 'numeric' : 'lexicographic',
            'lower'       => $minValue,
            'lowerStrict' => false,
            'upper'       => $maxValue,
            'upperStrict' => true,
        ];

        $this->assertEquals($expected, $filter->toArray());
    }
}
