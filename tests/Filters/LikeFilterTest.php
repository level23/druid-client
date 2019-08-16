<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Filters;

use Level23\Druid\Extractions\LookupExtraction;
use Level23\Druid\Filters\LikeFilter;
use tests\TestCase;

class LikeFilterTest extends TestCase
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
            'type'      => 'like',
            'dimension' => 'name',
            'pattern'   => 'D%',
            'escape'    => '#',
        ];

        if ($useExtractionFunction) {
            $filter                   = new LikeFilter('name', 'D%', '#', $extractionFunction);
            $expected['extractionFn'] = $extractionFunction->toArray();
        } else {
            $filter = new LikeFilter('name', 'D%', '#');
        }

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testEscapeDefaultCharacter()
    {
        $filter = new LikeFilter('name', 'D%');

        $this->assertEquals([
            'type'      => 'like',
            'dimension' => 'name',
            'pattern'   => 'D%',
            'escape'    => '\\',
        ], $filter->toArray());
    }
}