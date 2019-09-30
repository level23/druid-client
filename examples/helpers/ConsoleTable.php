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
            foreach ($row as $cell_key => $cell) {
                $valueLength = strlen(((string)$cell));
                $nameLength  = strlen(($cell_key));

                $length = $nameLength > $valueLength ? $nameLength : $valueLength;

                if (empty($columns[$cell_key]) || $columns[$cell_key] < $length) {
                    $columns[$cell_key] = $length;
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
        foreach ($data as $row_key => $row) {
            $table .= '| ';
            foreach ($row as $cell_key => $cell) {
                $table .= str_pad((string)$cell_key, $this->columns[$cell_key]) . ' | ';
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
        foreach ($data as $row_key => $row) {
            $table .= '| ';
            foreach ($row as $cell_key => $cell) {
                $table .= str_pad((string)$cell, $this->columns[$cell_key]) . ' | ';
            }
            $table .= PHP_EOL;
        }

        $table .= $this->buildRow();

        echo $table;
    }
}