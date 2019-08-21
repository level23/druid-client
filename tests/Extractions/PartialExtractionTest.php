<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Extractions;

use tests\TestCase;
use Level23\Druid\Extractions\PartialExtraction;

class PartialExtractionTest extends TestCase
{
    public function testExtractionFunction()
    {
        $regex              = '^[a-z]*$';
        $extractionFunction = new PartialExtraction($regex);

        $expected = [
            'type' => 'partial',
            'expr' => $regex,
        ];

        $this->assertEquals($expected, $extractionFunction->toArray());
    }
}