<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use Level23\Druid\Filters\LikeFilter;
use tests\TestCase;

class LikeFilterTest extends TestCase
{
    public function testFilter()
    {
        $filter = new LikeFilter('name', 'D%', '\\');

        $this->assertEquals([
            'type'      => 'like',
            'dimension' => 'name',
            'pattern'   => 'D%',
            'escape'    => '\\',
        ], $filter->getFilter());
    }
}