<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use tests\TestCase;
use Level23\Druid\Filters\SearchFilter;
use Level23\Druid\Extractions\LookupExtraction;

class SearchFilterTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            ['name', 'John', false],
            ['name', 'John', true],
            ['name', ['John', 'Jack'], true],
            ['name', ['John', 'Jack'], false],
            ['name', ['John', 'Jack'], null],
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
        ], $filter->toArray());
    }

    public function testExtractionFunction()
    {
        $extractionFunction = new LookupExtraction(
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
            'extractionFn' => $extractionFunction->toArray(),
        ], $filter->toArray());
    }
}
