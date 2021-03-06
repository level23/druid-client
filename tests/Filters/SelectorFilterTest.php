<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\Extractions\LookupExtraction;

class SelectorFilterTest extends TestCase
{
    /**
     * @param bool $useExtractionFunction
     * @testWith [true]
     *           [false]
     */
    public function testFilter(bool $useExtractionFunction): void
    {
        $extractionFunction = new LookupExtraction(
            'full_username', false
        );

        $expected = [
            'type'      => 'selector',
            'dimension' => 'name',
            'value'     => 'John',
        ];

        if ($useExtractionFunction) {
            $filter                   = new SelectorFilter('name', 'John', $extractionFunction);
            $expected['extractionFn'] = $extractionFunction->toArray();
        } else {
            $filter = new SelectorFilter('name', 'John');
        }

        $this->assertEquals($expected, $filter->toArray());
    }
}
