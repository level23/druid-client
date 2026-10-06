<?php
declare(strict_types=1);

namespace Level23\Druid\InputFormats;

class CsvInputFormat implements InputFormatInterface
{
    protected ?string $listDelimiter;

    /**
     * @var string[]|null
     */
    protected ?array $columns = null;

    protected ?bool $findColumnsFromHeader;

    protected int $skipHeaderRows;

    protected ?bool $tryParseNumbers;

    /**
     * @param string[]|null $columns               Specifies the columns of the data. The columns should be in the same
     *                                             order with the columns of your data.
     * @param string|null   $listDelimiter         A custom delimiter for multi-value dimensions.
     * @param bool|null     $findColumnsFromHeader If this is set, the task will find the column names from the header
     *                                             row. Note that skipHeaderRows will be applied before finding column
     *                                             names from the header. For example, if you set skipHeaderRows to 2 and
     *                                             findColumnsFromHeader to true, the task will skip the first two lines
     *                                             and then extract column information from the third line. columns will
     *                                             be ignored if this is set to true.
     * @param int           $skipHeaderRows        If this is set, the task will skip the first skipHeaderRows rows.
     * @param bool|null     $tryParseNumbers       If this is set, the task will attempt to parse numeric strings into
     *                                             long or double. Requires Druid 32 or higher.
     */
    public function __construct(
        ?array $columns = null,
        ?string $listDelimiter = null,
        ?bool $findColumnsFromHeader = null,
        int $skipHeaderRows = 0,
        ?bool $tryParseNumbers = null
    ) {
        $this->listDelimiter         = $listDelimiter;
        $this->columns               = $columns;
        $this->findColumnsFromHeader = $findColumnsFromHeader;
        $this->skipHeaderRows        = $skipHeaderRows;
        $this->tryParseNumbers       = $tryParseNumbers;
    }

    /**
     * Return the CsvInputFormat so that it can be used in a druid query.
     *
     * @return array<string,string|string[]|int|bool>
     */
    public function toArray(): array
    {
        $result = ['type' => 'csv'];

        if (!empty($this->columns)) {
            $result['columns'] = $this->columns;
        }

        if ($this->listDelimiter !== null) {
            $result['listDelimiter'] = $this->listDelimiter;
        }

        if ($this->findColumnsFromHeader !== null) {
            $result['findColumnsFromHeader'] = $this->findColumnsFromHeader;
        }

        if ($this->skipHeaderRows > 0) {
            $result['skipHeaderRows'] = $this->skipHeaderRows;
        }

        if ($this->tryParseNumbers !== null) {
            $result['tryParseNumbers'] = $this->tryParseNumbers;
        }

        return $result;
    }
}