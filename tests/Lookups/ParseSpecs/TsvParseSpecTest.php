<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Lookups\ParseSpecs;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Lookups\ParseSpecs\TsvParseSpec;

class TsvParseSpecTest extends TestCase
{
    public function testParseSpec(): void
    {
        $parseSpec = new TsvParseSpec(
            ['id', 'name', 'alias', 'costs', 'title'],
            'id',
            'title'
        );

        $this->assertEquals(
            [
                'format'         => 'tsv',
                'hasHeaderRow'   => false,
                'columns'        => ['id', 'name', 'alias', 'costs', 'title'],
                'keyColumn'      => 'id',
                'valueColumn'    => 'title',
                'skipHeaderRows' => 0,
            ],
            $parseSpec->toArray()
        );

        $parseSpec = new TsvParseSpec(
            null,
            'id',
            'title',
            "\t",
            "\n",
            true,
            3
        );

        $this->assertEquals(
            [
                'format'         => 'tsv',
                'hasHeaderRow'   => true,
                'keyColumn'      => 'id',
                'valueColumn'    => 'title',
                'skipHeaderRows' => 3,
                'columns'        => null,
                'delimiter'      => "\t",
                'listDelimiter'  => "\n",
            ],
            $parseSpec->toArray()
        );
    }
}
