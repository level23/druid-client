<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\InputFormats;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\InputFormats\TsvInputFormat;

class TsvInputFormatTest extends TestCase
{
    /**
     * @testWith [null, null, null, null, 0]
     *           ["\t", ".", ["a", "b"], false, 1]
     *           ["\t",".", ["a", "b"], true, 3]
     *           [null, null, null, true, 0]
     *           [null, null, null, null, 3]
     *           [null, null, ["a", "b"], null, 0]
     *           ["\t", "\\", null, null, 0]
     *
     *
     * @param string|null $delimiter
     * @param string|null $listDelimiter
     * @param array|null  $columns
     * @param bool|null   $findColumnsFromHeader
     * @param int         $skipHeaderRows
     *
     * @return void
     */
    public function testInputFormat(
        string $delimiter = null,
        string $listDelimiter = null,
        array $columns = null,
        bool $findColumnsFromHeader = null,
        int $skipHeaderRows = 0
    ): void {
        $input = new TsvInputFormat($columns, $delimiter, $listDelimiter, $findColumnsFromHeader, $skipHeaderRows);

        $expected = ['type' => 'tsv'];

        if (!empty($columns)) {
            $expected['columns'] = $columns;
        }

        if ($delimiter !== null) {
            $expected['delimiter'] = $delimiter;
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