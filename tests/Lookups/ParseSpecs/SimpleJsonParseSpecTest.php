<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Lookups\ParseSpecs;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Lookups\ParseSpecs\SimpleJsonParseSpec;

class SimpleJsonParseSpecTest extends TestCase
{
    public function testParseSpec(): void
    {
        $parseSpec = new SimpleJsonParseSpec();

        $this->assertEquals(['format' => 'simpleJson'], $parseSpec->toArray());
    }
}
