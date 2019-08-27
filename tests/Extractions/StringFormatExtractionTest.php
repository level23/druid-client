<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Extractions;

use tests\TestCase;
use Level23\Druid\Extractions\StringFormatExtraction;

class StringFormatExtractionTest extends TestCase
{
    /**
     * @testWith ["[%s]"]
     *           ["%02d"]
     * @param string $sprintf
     */
    public function testExtraction(string $sprintf)
    {
        $extraction = new StringFormatExtraction($sprintf);

        $this->assertEquals([
            'name'   => 'stringFormat',
            'format' => $sprintf,
        ], $extraction->toArray());
    }
}