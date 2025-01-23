<?php
declare(strict_types=1);

namespace Level23\Druid\Lookups\ParseSpecs;

/**
 * @internal
 */
class TsvParseSpec implements ParseSpecInterface
{
    /**
     * Specify the TSV parse spec.
     *
     * @param array<int,string>|null $columns
     * @param string|null            $keyColumn
     * @param string|null            $valueColumn
     * @param string|null            $delimiter
     * @param string|null            $listDelimiter
     * @param bool                   $hasHeaderRow
     * @param int                    $skipHeaderRows
     */
    public function __construct(
        protected null|array $columns,
        protected ?string $keyColumn = null,
        protected ?string $valueColumn = null,
        protected ?string $delimiter = null,
        protected ?string $listDelimiter = null,
        protected bool $hasHeaderRow = false,
        protected int $skipHeaderRows = 0
    ) {

    }

    /**
     * @return array<string,bool|array<int,string>|string|int|null>
     */
    public function toArray(): array
    {
        $response = [
            'format'       => 'tsv',
            'columns'      => $this->columns,
            'hasHeaderRow' => $this->hasHeaderRow,
        ];

        if ($this->keyColumn !== null) {
            $response['keyColumn'] = $this->keyColumn;
        }
        if ($this->valueColumn !== null) {
            $response['valueColumn'] = $this->valueColumn;
        }
        if ($this->delimiter !== null) {
            $response['delimiter'] = $this->delimiter;
        }
        if ($this->listDelimiter !== null) {
            $response['listDelimiter'] = $this->listDelimiter;
        }
        $response['skipHeaderRows'] = $this->skipHeaderRows;

        return $response;
    }
}