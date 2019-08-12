<?php
declare(strict_types=1);

namespace tests\Level23\Druid\ExtractionFunctions;

use Level23\Druid\ExtractionFunctions\PartialExtractionFunction;
use tests\TestCase;

class PartialExtractionFunctionTest extends TestCase
{
    public function testExtractionFunction()
    {
        $regex              = '^[a-z]*$';
        $extractionFunction = new PartialExtractionFunction($regex);

        $expected = [
            'type' => 'partial',
            'expr' => $regex,
        ];

        $this->assertEquals($expected, $extractionFunction->getExtractionFunction());
    }
}