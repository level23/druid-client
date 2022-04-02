<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Dimensions;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Dimensions\TimestampSpec;

class TimestampSpecTest extends TestCase
{
    public function testTimestampSpec(): void
    {
        $timestamp = new TimestampSpec('myCol', 'auto');

        $this->assertEquals([
            'column' => 'myCol',
            'format' => 'auto',
        ], $timestamp->toArray());

        $timestamp = new TimestampSpec('timestamp', 'posix', '2021-12-01T11:00:12');

        $this->assertEquals([
            'column'       => 'timestamp',
            'format'       => 'posix',
            'missingValue' => '2021-12-01T11:00:12',
        ], $timestamp->toArray());
    }
}