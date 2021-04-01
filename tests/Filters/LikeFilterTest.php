<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Filters;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Filters\LikeFilter;
use Level23\Druid\Extractions\LookupExtraction;

class LikeFilterTest extends TestCase
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

    public function testEscapeDefaultCharacter(): void
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
