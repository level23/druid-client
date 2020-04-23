<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Extractions;

use tests\Level23\Druid\TestCase;
use Level23\Druid\Extractions\SubstringExtraction;

class SubstringExtractionTest extends TestCase
{
    public function testExtraction()
    {
        $extraction = new SubstringExtraction(4);

        $this->assertEquals([
            'type'  => 'substring',
            'index' => 4,
        ], $extraction->toArray());

        $extraction = new SubstringExtraction(6, 2);

        $this->assertEquals([
            'type'   => 'substring',
            'index'  => 6,
            'length' => 2,
        ], $extraction->toArray());
    }
}
