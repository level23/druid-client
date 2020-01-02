<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Extractions;

use tests\TestCase;
use Level23\Druid\Extractions\JavascriptExtraction;

class JavascriptExtractionTest extends TestCase
{
    /**
     * @testWith [true]
     *           [false]
     */
    public function testExtractionFunction(bool $injective)
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

    public function testExtractionFunctionDefaults()
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
