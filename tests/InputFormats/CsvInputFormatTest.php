<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\InputFormats;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\InputFormats\CsvInputFormat;

class CsvInputFormatTest extends TestCase
{
    /**
     * @testWith [null, null, null, 0]
     *           [".", ["a", "b"], false, 1]
     *           [".", ["a", "b"], true, 3]
     *           [null, null, true, 0]
     *           [null, null, null, 3]
     *           [null, ["a", "b"], null, 0]
     *           ["\\", null, null, 0]
     *
     *
     * @param string|null   $listDelimiter
     * @param string[]|null $columns
     * @param bool|null     $findColumnsFromHeader
     * @param int           $skipHeaderRows
     *
     * @return void
     */
    public function testInputFormat(
        ?string $listDelimiter = null,
        ?array $columns = null,
        ?bool $findColumnsFromHeader = null,
        int $skipHeaderRows = 0
    ): void {
        $input = new CsvInputFormat($columns, $listDelimiter, $findColumnsFromHeader, $skipHeaderRows);

        $expected = ['type' => 'csv'];

        if (!empty($columns)) {
            $expected['columns'] = $columns;
        }

        if ($listDelimiter !== null) {
            $expected['listDelimiter'] = $listDelimiter;
        }

        if ($findColumnsFromHeader !== null) {
            $expected['findColumnsFromHeader'] = $findColumnsFromHeader;
        }

        if ($skipHeaderRows > 0) {
            $expected['skipHeaderRows'] = $skipHeaderRows;
        }

        $this->assertEquals($expected, $input->toArray());
    }
}