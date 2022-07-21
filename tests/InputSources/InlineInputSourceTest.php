<?php

declare(strict_types=1);

namespace Level23\Druid\Tests\InputSources;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\InputSources\InlineInputSource;

class InlineInputSourceTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testInlineInputSourceJson(): void
    {
        $csv    = InlineInputSource::dataToCsv([
            [true, 18, "male"],
            [false, 92, "female"],
        ]);
        $inline = new InlineInputSource($csv);

        $this->assertEquals([
            'type' => 'inline',
            'data' => $csv,
        ], $inline->toArray());

        $json   = InlineInputSource::dataToJson([
            [true, 18, "male"],
            [false, 92, "female"],
        ]);
        $inline = new InlineInputSource($json);

        $this->assertEquals([
            'type' => 'inline',
            'data' => $json,
        ], $inline->toArray());
    }
}