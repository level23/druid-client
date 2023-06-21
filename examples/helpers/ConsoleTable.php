<?php

/**
 * This is just a simple helper class which displays the result set as a nice console table.
 *
 * Class ConsoleTable
 */
class ConsoleTable
{
    /**
     * @var array<string,int>
     */
    protected array $columns = [];

    /**
     * @param array<int|string, string|int|array<string|int, array<mixed>|int|string>> $data
     *
     * @return void
     */
    protected function detectColumnSize(array $data): void
    {
        // Find the longest string in each column
        $columns = [];
        foreach ($data as $row) {
            $row = (array)$row;
            foreach ($row as $cellKey => $cell) {
                if (is_array($cell)) {
                    $cell = implode(', ', $cell);
                }

                $valueLength = strlen(strval($cell));
                $nameLength  = strlen(strval($cellKey));

                $length = max($nameLength, $valueLength);

                if (empty($columns[$cellKey]) || $columns[$cellKey] < $length) {
                    $columns[strval($cellKey)] = $length;
                }
            }
        }

        $this->columns = $columns;
    }

    protected function buildRow(): string
    {
        $table = '+';
        foreach ($this->columns as $length) {
            $table .= str_repeat('-', $length + 1) . '-+';
        }
        $table .= PHP_EOL;

        return $table;
    }

    /**
     * @param array<int|string, string|int|array<string|int, array<mixed>|int|string>> $data
     *
     * @return string
     */
    protected function buildHeader(array $data): string
    {
        $table = $this->buildRow();

        // Build the header
        foreach ($data as $row) {
            $table .= '| ';
            $row = (array)$row;
            foreach ($row as $cellKey => $cell) {
                $cellKey = strval($cellKey);
                $table .= str_pad($cellKey, $this->columns[$cellKey]) . ' | ';
            }
            $table .= PHP_EOL;

            break;
        }

        $table .= $this->buildRow();

        return $table;
    }

    /**
     * @param array<int|string, string|int|array<string|int, array<mixed>|int|string>> $data
     */
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