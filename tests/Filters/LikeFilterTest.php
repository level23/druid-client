<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\LikeFilter;

class LikeFilterTest extends TestCase
{
    public function testFilter(): void
    {
        $expected = [
            'type'      => 'like',
            'dimension' => 'name',
            'pattern'   => 'D%',
            'escape'    => '#',
        ];

        $filter = new LikeFilter('name', 'D%', '#');

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testEscapeDefaultCharacter(): void
    {
        $filter = new LikeFilter('name', 'D%');

        $this->assertEquals([
            'type'      => 'like',
            'dimension' => 'name',
            'pattern'   => 'D%',
            'escape'    => '\\',
        ], $filter->toArray());
    }
}
