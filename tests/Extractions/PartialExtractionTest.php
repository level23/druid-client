<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Extractions;

use Level23\Druid\Extractions\PartialExtraction;
use tests\TestCase;

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