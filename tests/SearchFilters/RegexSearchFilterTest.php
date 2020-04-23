<?php
declare(strict_types=1);

namespace tests\Level23\Druid\SearchFilters;

use tests\Level23\Druid\TestCase;
use Level23\Druid\SearchFilters\RegexSearchFilter;

class RegexSearchFilterTest extends TestCase
{
    public function testSearchFilter()
    {
        $filter = new RegexSearchFilter('^wiki');

        $this->assertEquals([
            'type'    => 'regex',
            'pattern' => '^wiki',
        ], $filter->toArray());
    }
}
