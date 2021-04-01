<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Extractions;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Extractions\PartialExtraction;

class PartialExtractionTest extends TestCase
{
    public function testExtractionFunction(): void
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
