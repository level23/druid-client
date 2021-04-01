<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Extractions;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Extractions\UpperExtraction;

class UpperExtractionTest extends TestCase
{
    /**
     * @testWith [null]
     *           ["fr"]
     * @param null|string $locale
     */
    public function testExtraction($locale): void
    {
        $extraction = new UpperExtraction($locale);

        $expected = [
            'type' => 'upper',
        ];

        if ($locale) {
            $expected['locale'] = $locale;
        }

        $this->assertEquals($expected, $extraction->toArray());
    }
}
