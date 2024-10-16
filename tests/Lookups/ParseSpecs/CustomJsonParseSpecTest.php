<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Lookups\ParseSpecs;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Lookups\ParseSpecs\CustomJsonParseSpec;

class CustomJsonParseSpecTest extends TestCase
{
    public function testParseSpec(): void
    {
        $parseSpec = new CustomJsonParseSpec(
            'id',
            'title',
        );

        $this->assertEquals(
            [
                'format'         => 'customJson',
                'keyFieldName'   => 'id',
                'valueFieldName' => 'title',
            ],
            $parseSpec->toArray()
        );
    }
}
