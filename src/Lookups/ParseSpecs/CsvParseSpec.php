<?php
declare(strict_types=1);

namespace Level23\Druid\Lookups\ParseSpecs;

/**
 * @internal
 */
class CsvParseSpec implements ParseSpecInterface
{
    /**
     * Specify the CSV parse spec.
     *
     * @param array<int,string>|null $columns
     * @param string|null            $keyColumn
     * @param string|null            $valueColumn
     * @param bool                   $hasHeaderRow
     * @param int                    $skipHeaderRows
     */
    public function __construct(
        protected ?array $columns,
        protected ?string $keyColumn = null,
        protected ?string $valueColumn = null,
        protected bool $hasHeaderRow = false,
        protected int $skipHeaderRows = 0
    ) {

    }

    /**
     * @return array<string,bool|array<int,string>|string|int>
     */
    public function toArray(): array
    {
        $response = [
            'format'       => 'csv',
            'hasHeaderRow' => $this->hasHeaderRow,
        ];

        if ($this->columns !== null) {
            $response['columns'] = $this->columns;
        }

        if ($this->keyColumn !== null) {
            $response['keyColumn'] = $this->keyColumn;
        }
        if ($this->valueColumn !== null) {
            $response['valueColumn'] = $this->valueColumn;
        }
        $response['skipHeaderRows'] = $this->skipHeaderRows;

        return $response;
    }
}