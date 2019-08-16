<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use Level23\Druid\Extractions\LookupExtraction;
use Level23\Druid\Filters\JavascriptFilter;
use tests\TestCase;

class JavascriptFilterTest extends TestCase
{
    /**
     * @param bool $useExtractionFunction
     * @testWith [true]
     *           [false]
     */
    public function testFilter(bool $useExtractionFunction)
    {
        $extractionFunction = new LookupExtraction(
            'singup_by_member', false
        );

        $function = "function(x) { return(x >= 'bar' && x <= 'foo') }";

        $expected = [
            'type'      => 'javascript',
            'dimension' => 'name',
            'function'  => $function,
        ];

        if ($useExtractionFunction) {
            $filter                   = new JavascriptFilter('name', $function, $extractionFunction);
            $expected['extractionFn'] = $extractionFunction->toArray();
        } else {
            $filter = new JavascriptFilter('name', $function);
        }

        $this->assertEquals($expected, $filter->toArray());
    }
}