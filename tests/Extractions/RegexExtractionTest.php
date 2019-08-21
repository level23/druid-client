<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Extractions;

use tests\TestCase;
use Level23\Druid\Extractions\RegexExtraction;

class RegexExtractionTest extends TestCase
{
    public function testExtractionWithDefaults()
    {
        $extraction = new RegexExtraction('^[0-9]*$');

        $this->assertEquals([
            'type'                    => 'regex',
            'expr'                    => '^[0-9]*$',
            'index'                   => 1,
            'replaceMissingValue'     => false,
            'replaceMissingValueWith' => null,
        ], $extraction->toArray());
    }

    public function testExtractionWithReplacement()
    {
        $extraction = new RegexExtraction('^[0-9]*$', 3, 'Unknown');

        $this->assertEquals([
            'type'                    => 'regex',
            'expr'                    => '^[0-9]*$',
            'index'                   => 3,
            'replaceMissingValue'     => true,
            'replaceMissingValueWith' => 'Unknown',

        ], $extraction->toArray());
    }
}
