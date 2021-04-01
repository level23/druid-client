<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Extractions;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Extractions\JavascriptExtraction;

class JavascriptExtractionTest extends TestCase
{
    /**
     * @testWith [true]
     *           [false]
     *
     * @param bool $injective
     */
    public function testExtractionFunction(bool $injective): void
    {
        $str        = 'function(x) { return "y"; }';
        $extraction = new JavascriptExtraction($str, $injective);

        $expected = [
            'type'      => 'javascript',
            'function'  => $str,
            'injective' => $injective,
        ];

        $this->assertEquals($expected, $extraction->toArray());
    }

    public function testExtractionFunctionDefaults(): void
    {
        $str        = 'function(x) { return "y"; }';
        $extraction = new JavascriptExtraction($str);

        $expected = [
            'type'      => 'javascript',
            'function'  => $str,
            'injective' => false,
        ];

        $this->assertEquals($expected, $extraction->toArray());
    }
}
