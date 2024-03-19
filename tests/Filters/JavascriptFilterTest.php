<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\JavascriptFilter;

class JavascriptFilterTest extends TestCase
{
    public function testFilter(): void
    {
        $function = "function(x) { return(x >= 'bar' && x <= 'foo') }";

        $expected = [
            'type'      => 'javascript',
            'dimension' => 'name',
            'function'  => $function,
        ];

        $filter = new JavascriptFilter('name', $function);

        $this->assertEquals($expected, $filter->toArray());
    }
}
