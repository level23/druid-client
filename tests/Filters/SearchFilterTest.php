<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\SearchFilter;

class SearchFilterTest extends TestCase
{
    /**
     * @return array<array<string|string[]|bool|null>>
     */
    public static function dataProvider(): array
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
     * @param string          $dimension
     * @param string|string[] $valueOrValues
     * @param bool            $caseSensitive
     */
    public function testFilter(string $dimension, array|string $valueOrValues, ?bool $caseSensitive): void
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
}
