<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Extractions;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Extractions\LowerExtraction;

class LowerExtractionTest extends TestCase
{
    /**
     * @testWith [null]
     *           ["fr"]
     * @param string|null $locale
     */
    public function testExtraction(?string $locale): void
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
