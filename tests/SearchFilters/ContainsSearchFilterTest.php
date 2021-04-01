<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\SearchFilters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\SearchFilters\ContainsSearchFilter;

class ContainsSearchFilterTest extends TestCase
{
    /**
     * @testWith [true]
     *           [false]
     *
     * @param bool $caseSensitive
     */
    public function testSearchFilter(bool $caseSensitive): void
    {
        $filter = new ContainsSearchFilter('wikipedia', $caseSensitive);

        $this->assertEquals([
            'type'  => ($caseSensitive ? 'contains' : 'insensitive_contains'),
            'value' => 'wikipedia',
        ], $filter->toArray());
    }
}
