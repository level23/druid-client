<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Extractions;

use tests\TestCase;
use Level23\Druid\Extractions\LowerExtraction;

class LowerExtractionTest extends TestCase
{
    /**
     * @testWith [null]
     *           ["fr"]
     * @param null|string $locale
     */
    public function testExtraction($locale)
    {
        $extraction = new LowerExtraction($locale);

        $expected = [
            'type' => 'lower',
        ];

        if ($locale) {
            $expected['locale'] = $locale;
        }

        $this->assertEquals($expected, $extraction->toArray());
    }
}