<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Lookups\ParseSpecs;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Lookups\ParseSpecs\CsvParseSpec;

class CsvParseSpecTest extends TestCase
{
    public function testParseSpec(): void
    {
        $parseSpec = new CsvParseSpec(
            ['id', 'name', 'alias', 'costs', 'title'],
            'id',
            'title',
        );

        $this->assertEquals(
            [
                'format'         => 'csv',
                'hasHeaderRow'   => false,
                'columns'        => ['id', 'name', 'alias', 'costs', 'title'],
                'keyColumn'      => 'id',
                'valueColumn'    => 'title',
                'skipHeaderRows' => 0,
            ],
            $parseSpec->toArray()
        );

        $parseSpec = new CsvParseSpec(
            null,
            'id',
            'title',
            true,
            2
        );

        $this->assertEquals(
            [
                'format'         => 'csv',
                'hasHeaderRow'   => true,
                'keyColumn'      => 'id',
                'valueColumn'    => 'title',
                'skipHeaderRows' => 2,
            ],
            $parseSpec->toArray()
        );
    }
}
