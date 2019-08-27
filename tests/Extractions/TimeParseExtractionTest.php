<?php

declare(strict_types=1);

namespace tests\Level23\Druid\Extractions;

use tests\TestCase;
use Level23\Druid\Extractions\TimeParseExtraction;

class TimeParseExtractionTest extends TestCase
{
    public function testExtraction()
    {
        $extraction = new TimeParseExtraction("yyyy.MM.dd G 'at' hh:mm:ss a zzz", "K:mm a, vvv");

        $this->assertEquals([
            'type'         => 'time',
            'timeFormat'   => "yyyy.MM.dd G 'at' hh:mm:ss a zzz",
            'resultFormat' => "K:mm a, vvv",
        ], $extraction->toArray());
    }
}