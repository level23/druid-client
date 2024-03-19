<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\InFilter;

class InFilterTest extends TestCase
{
    public function testFilter(): void
    {
        $filter = new InFilter('name', ['John', 'Jan', 'Jack']);

        $this->assertEquals([
            'type'      => 'in',
            'dimension' => 'name',
            'values'    => ['John', 'Jan', 'Jack'],
        ], $filter->toArray());
    }

    public function testFilterWithAssociativeArray(): void
    {
        $filter = new InFilter('name', ['name1' => 'John', 'name2' => 'Jan', 'name3' => 'Jack']);

        $this->assertEquals([
            'type'      => 'in',
            'dimension' => 'name',
            'values'    => ['John', 'Jan', 'Jack'],
        ], $filter->toArray());
    }
}
