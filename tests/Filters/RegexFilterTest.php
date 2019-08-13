<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use Level23\Druid\Extractions\LookupExtraction;
use Level23\Druid\Filters\RegexFilter;
use tests\TestCase;

class RegexFilterTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param bool $useExtractionFunction
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
            $expected['extractionFn'] = $extractionFunction->getExtractionFunction();
        } else {
            $filter = new RegexFilter('name', '^[a-z]*$');
        }

        $this->assertEquals($expected, $filter->getFilter());
    }
}