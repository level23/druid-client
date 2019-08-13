<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use Level23\Druid\ExtractionFunctions\LookupExtractionFunction;
use Level23\Druid\Filters\SearchFilter;
use tests\TestCase;

class SearchFilterTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            ['name', 'Piet', false],
            ['name', 'Piet', true],
            ['name', ['Piet', 'Klaas'], true],
            ['name', ['Piet', 'Klaas'], false],
            ['name', ['Piet', 'Klaas'], null],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string       $dimension
     * @param string|array $valueOrValues
     * @param bool         $caseSensitive
     */
    public function testFilter(string $dimension, $valueOrValues, ?bool $caseSensitive)
    {
        if ($caseSensitive !== null) {
            $filter = new SearchFilter($dimension, $valueOrValues, $caseSensitive);
        } else {
            $filter = new SearchFilter($dimension, $valueOrValues);
        }

        if (is_array($valueOrValues)) {
            $expectedQuery = [
                'type'          => 'fragment',
                'values'        => $valueOrValues,
                'caseSensitive' => ($caseSensitive ?: false),
            ];
        } else {
            $expectedQuery = [
                'type'          => 'contains',
                'value'         => $valueOrValues,
                'caseSensitive' => ($caseSensitive ?: false),
            ];
        }

        $this->assertEquals([
            'type'      => 'search',
            'dimension' => $dimension,
            'query'     => $expectedQuery,
        ], $filter->getFilter());
    }

    public function testExtractionFunction()
    {
        $extractionFunction = new LookupExtractionFunction(
            'full_username', false
        );

        $filter = new SearchFilter('name', 'john', false, $extractionFunction);

        $this->assertEquals([
            'type'         => 'search',
            'dimension'    => 'name',
            'query'        => [
                'type'          => 'contains',
                'value'         => 'john',
                'caseSensitive' => false,
            ],
            'extractionFn' => $extractionFunction->getExtractionFunction(),
        ], $filter->getFilter());
    }
}