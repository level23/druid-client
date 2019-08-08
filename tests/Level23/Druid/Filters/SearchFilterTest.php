<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

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
        ];
    }

    /**
     * @dataProvider dataProvider
     * @param string $dimension
     * @param string|array $valueOrValues
     * @param bool $caseSensitive
     */
    public function testFilter(string $dimension, $valueOrValues, bool $caseSensitive)
    {
        $filter = new SearchFilter($dimension, $valueOrValues, $caseSensitive);

        if (is_array($valueOrValues)) {
            $expectedQuery = [
                'type'          => 'fragment',
                'values'        => $valueOrValues,
                'caseSensitive' => $caseSensitive,
            ];
        } else {
            $expectedQuery = [
                'type'          => 'contains',
                'value'         => $valueOrValues,
                'caseSensitive' => $caseSensitive,
            ];
        }

        $this->assertEquals([
            'type'      => 'search',
            'dimension' => $dimension,
            'query'     => $expectedQuery,
        ], $filter->getFilter());
    }
}