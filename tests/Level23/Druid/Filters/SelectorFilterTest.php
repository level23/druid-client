<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use Level23\Druid\ExtractionFunctions\LookupExtractionFunction;
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
        $extractionFunction = new LookupExtractionFunction(
            'full_username', false
        );

        $expected = [
            'type'      => 'selector',
            'dimension' => 'name',
            'value'     => 'Piet',
        ];

        if ($useExtractionFunction) {
            $filter                   = new SelectorFilter('name', 'Piet', $extractionFunction);
            $expected['extractionFn'] = $extractionFunction->getExtractionFunction();
        } else {
            $filter = new SelectorFilter('name', 'Piet');
        }

        $this->assertEquals($expected, $filter->getFilter());
    }
}