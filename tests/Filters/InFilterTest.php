<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use tests\Level23\Druid\TestCase;
use Level23\Druid\Filters\InFilter;
use Level23\Druid\Extractions\SubstringExtraction;

class InFilterTest extends TestCase
{
    public function testFilter()
    {
        $filter = new InFilter('name', ['John', 'Jan', 'Jack']);

        $this->assertEquals([
            'type'      => 'in',
            'dimension' => 'name',
            'values'    => ['John', 'Jan', 'Jack'],
        ], $filter->toArray());
    }

    public function testFilterWithExtraction()
    {
        $substring = new SubstringExtraction(1, 2);
        $filter    = new InFilter('name', ['John', 'Jan', 'Jack'], $substring);

        $this->assertEquals([
            'type'         => 'in',
            'dimension'    => 'name',
            'values'       => ['John', 'Jan', 'Jack'],
            'extractionFn' => $substring->toArray(),
        ], $filter->toArray());
    }

    public function testFilterWithAssociativeArray()
    {
        $substring = new SubstringExtraction(1, 2);
        $filter    = new InFilter('name', ['name1' => 'John', 'name2' => 'Jan', 'name3' => 'Jack'], $substring);

        $this->assertEquals([
            'type'         => 'in',
            'dimension'    => 'name',
            'values'       => ['John', 'Jan', 'Jack'],
            'extractionFn' => $substring->toArray(),
        ], $filter->toArray());
    }
}
