<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\InputFormats;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\InputFormats\LinesInputFormat;

class LinesInputFormatTest extends TestCase
{
    public function testInputFormat(): void
    {
        $this->assertEquals(['type' => 'lines'], (new LinesInputFormat())->toArray());
    }
}
