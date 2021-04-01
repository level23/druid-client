<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Extractions;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Extractions\RegexExtraction;

class RegexExtractionTest extends TestCase
{
    public function testExtractionWithDefaults(): void
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

    public function testExtractionWithReplacement(): void
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
