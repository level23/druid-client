<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

use tests\TestCase;

class SubstringExtractionTest extends TestCase
{
    public function testExtraction()
    {
        $extraction = new SubstringExtraction(4);

        $this->assertEquals([
            'type'  => 'substring',
            'index' => 4,
        ], $extraction->getExtractionFunction());

        $extraction = new SubstringExtraction(6, 2);

        $this->assertEquals([
            'type'   => 'substring',
            'index'  => 6,
            'length' => 2,
        ], $extraction->getExtractionFunction());
    }
}
