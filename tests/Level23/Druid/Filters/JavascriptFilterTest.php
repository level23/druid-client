<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use Level23\Druid\Filters\JavascriptFilter;
use tests\TestCase;

class JavascriptFilterTest extends TestCase
{
    public function testFilter()
    {
        $function = "function(x) { return(x >= 'bar' && x <= 'foo') }";
        $filter   = new JavascriptFilter('name', $function);

        $this->assertEquals([
            'type'      => 'javascript',
            'dimension' => 'name',
            'function'  => $function,
        ], $filter->getFilter());
    }
}