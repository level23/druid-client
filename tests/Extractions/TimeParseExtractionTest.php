<?php

declare(strict_types=1);

namespace Level23\Druid\Tests\Extractions;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Extractions\TimeParseExtraction;

class TimeParseExtractionTest extends TestCase
{
    public function testExtraction(): void
    {
        $extraction = new TimeParseExtraction(
            "yyyy.MM.dd G 'at' hh:mm:ss a zzz",
            "K:mm a, vvv",
            false);

        $this->assertEquals([
            'type'         => 'time',
            'timeFormat'   => "yyyy.MM.dd G 'at' hh:mm:ss a zzz",
            'resultFormat' => "K:mm a, vvv",
            'joda'         => false,
        ], $extraction->toArray());
    }

    public function testExtractionDefaults(): void
    {
        $extraction = new TimeParseExtraction(
            "yyyy.MM.dd G 'at' hh:mm:ss a zzz",
            "K:mm a, vvv");

        $this->assertEquals([
            'type'         => 'time',
            'timeFormat'   => "yyyy.MM.dd G 'at' hh:mm:ss a zzz",
            'resultFormat' => "K:mm a, vvv",
            'joda'         => true,
        ], $extraction->toArray());
    }
}
