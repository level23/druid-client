<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use Level23\Druid\Extractions\LookupExtraction;
use Level23\Druid\Filters\SelectorFilter;
use tests\TestCase;

class SelectorFilterTest extends TestCase
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
            'type'      => 'selector',
            'dimension' => 'name',
            'value'     => 'Piet',
        ];

        if ($useExtractionFunction) {
            $filter                   = new SelectorFilter('name', 'Piet', $extractionFunction);
            $expected['extractionFn'] = $extractionFunction->toArray();
        } else {
            $filter = new SelectorFilter('name', 'Piet');
        }

        $this->assertEquals($expected, $filter->toArray());
    }
}