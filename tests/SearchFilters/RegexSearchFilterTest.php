<?php
declare(strict_types=1);

namespace tests\Level23\Druid\SearchFilters;

use tests\TestCase;
use Level23\Druid\SearchFilters\RegexSearchFilter;

class RegexSearchFilterTest extends TestCase
{
    /**
     * @testWith [true]
     *           [false]
     *
     * @param bool $caseSensitive
     */
    public function testSearchFilter(bool $caseSensitive)
    {
        $filter = new RegexSearchFilter('^wiki');

        $this->assertEquals([
            'type'    => 'regex',
            'pattern' => '^wiki',
        ], $filter->toArray());
    }
}