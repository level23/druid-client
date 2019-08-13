<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use Level23\Druid\ExtractionFunctions\LookupExtractionFunction;
use Level23\Druid\Filters\LikeFilter;
use tests\TestCase;

class LikeFilterTest extends TestCase
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
            'type'      => 'like',
            'dimension' => 'name',
            'pattern'   => 'D%',
            'escape'    => '#',
        ];

        if ($useExtractionFunction) {
            $filter                   = new LikeFilter('name', 'D%', '#', $extractionFunction);
            $expected['extractionFn'] = $extractionFunction->getExtractionFunction();
        } else {
            $filter = new LikeFilter('name', 'D%', '#');
        }

        $this->assertEquals($expected, $filter->getFilter());
    }

    public function testEscapeDefaultCharacter()
    {
        $filter = new LikeFilter('name', 'D%');

        $this->assertEquals([
            'type'      => 'like',
            'dimension' => 'name',
            'pattern'   => 'D%',
            'escape'    => '\\',
        ], $filter->getFilter());
    }
}