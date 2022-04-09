<?php

/**
 * This is just a simple helper class which displays the result set as a nice console table.
 *
 * Class ConsoleTable
 */
class ConsoleTable
{
    protected $columns = [];

    protected function detectColumnSize(array $data)
    {
        // Find longest string in each column
        $columns = [];
        foreach ($data as $row_key => $row) {
            foreach ($row as $cellKey => $cell) {
                if (is_array($cell)) {
                    $cell = implode(', ', $cell);
                }

                $valueLength = strlen(((string)$cell));
                $nameLength  = strlen(($cellKey));

                $length = $nameLength > $valueLength ? $nameLength : $valueLength;

                if (empty($columns[$cellKey]) || $columns[$cellKey] < $length) {
                    $columns[$cellKey] = $length;
                }
            }
        }

        $this->columns = $columns;
    }

    protected function buildRow(): string
    {
        $table = '+';
        foreach ($this->columns as $column => $length) {
            $table .= str_repeat('-', $length + 1) . '-+';
        }
        $table .= PHP_EOL;

        return $table;
    }

    protected function buildHeader(array $data): string
    {
        $table = '';

        $table .= $this->buildRow();

        // Build the header
        foreach ($data as $row) {
            $table .= '| ';
            foreach ($row as $cellKey => $cell) {
                $table .= str_pad((string)$cellKey, $this->columns[$cellKey]) . ' | ';
            }
            $table .= PHP_EOL;

            break;
        }

        $table .= $this->buildRow();

        return $table;
    }

    public function __construct(array $data)
    {
        $this->detectColumnSize($data);

        $table = $this->buildHeader($data);

        // Output table, padding columns
        foreach ($data as $row) {
            $table .= '| ';

            foreach ($this->columns as $column => $length) {
                $value = $row[$column] ?? '';
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $table .= str_pad((string)($value), $length) . ' | ';
            }
            $table .= PHP_EOL;
        }

        $table .= $this->buildRow();

        echo $table;
    }
}