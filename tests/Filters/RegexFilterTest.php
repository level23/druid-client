<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\RegexFilter;
use Level23\Druid\Extractions\LookupExtraction;

class RegexFilterTest extends TestCase
{
    /**
     * @param bool $useExtractionFunction
     * @testWith [true]
     *           [false]
     */
    public function testFilter(bool $useExtractionFunction)
    {
        $extractionFunction = new LookupExtraction(
            'full_username', false
        );

        $expected = [
            'type'      => 'regex',
            'dimension' => 'name',
            'pattern'   => '^[a-z]*$',
        ];

        if ($useExtractionFunction) {
            $filter                   = new RegexFilter('name', '^[a-z]*$', $extractionFunction);
            $expected['extractionFn'] = $extractionFunction->toArray();
        } else {
            $filter = new RegexFilter('name', '^[a-z]*$');
        }

        $this->assertEquals($expected, $filter->toArray());
    }
}
