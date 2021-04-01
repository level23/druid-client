<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\SearchFilters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\SearchFilters\RegexSearchFilter;

class RegexSearchFilterTest extends TestCase
{
    public function testSearchFilter(): void
    {
        $filter = new RegexSearchFilter('^wiki');

        $this->assertEquals([
            'type'    => 'regex',
            'pattern' => '^wiki',
        ], $filter->toArray());
    }
}
