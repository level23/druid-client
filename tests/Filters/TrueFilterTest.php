<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\TrueFilter;

class TrueFilterTest extends TestCase
{
    public function testFilter(): void
    {
        $filter = new TrueFilter();

        $this->assertEquals([
            'type' => 'true',
        ], $filter->toArray());
    }
}
