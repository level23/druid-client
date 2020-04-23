<?php
declare(strict_types=1);

namespace tests\Level23\Druid\SearchFilters;

use tests\Level23\Druid\TestCase;
use Level23\Druid\SearchFilters\ContainsSearchFilter;

class ContainsSearchFilterTest extends TestCase
{
    /**
     * @testWith [true]
     *           [false]
     *
     * @param bool $caseSensitive
     */
    public function testSearchFilter(bool $caseSensitive)
    {
        $filter = new ContainsSearchFilter('wikipedia', $caseSensitive);

        $this->assertEquals([
            'type'  => ($caseSensitive ? 'contains' : 'insensitive_contains'),
            'value' => 'wikipedia',
        ], $filter->toArray());
    }
}
