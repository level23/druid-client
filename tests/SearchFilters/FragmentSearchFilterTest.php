<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\SearchFilters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\SearchFilters\FragmentSearchFilter;

class FragmentSearchFilterTest extends TestCase
{
    /**
     * @testWith [true]
     *           [false]
     *
     * @param bool $caseSensitive
     */
    public function testSearchFilter(bool $caseSensitive): void
    {
        $filter = new FragmentSearchFilter(['wiki', 'pedia'], $caseSensitive);

        $this->assertEquals([
            'type'           => 'fragment',
            'values'         => ['wiki', 'pedia'],
            'case_sensitive' => $caseSensitive,
        ], $filter->toArray());
    }
}
