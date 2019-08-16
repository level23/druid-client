<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use Level23\Druid\Extractions\SubstringExtraction;
use Level23\Druid\Filters\InFilter;
use tests\TestCase;

class InFilterTest extends TestCase
{
    public function testFilter()
    {
        $filter = new InFilter('name', ['Piet', 'Jan', 'Klaas']);

        $this->assertEquals([
            'type'      => 'in',
            'dimension' => 'name',
            'values'    => ['Piet', 'Jan', 'Klaas'],
        ], $filter->toArray());
    }

    public function testFilterWithExtraction()
    {
        $substring = new SubstringExtraction(1, 2);
        $filter    = new InFilter('name', ['Piet', 'Jan', 'Klaas'], $substring);

        $this->assertEquals([
            'type'         => 'in',
            'dimension'    => 'name',
            'values'       => ['Piet', 'Jan', 'Klaas'],
            'extractionFn' => $substring->toArray(),
        ], $filter->toArray());
    }
}