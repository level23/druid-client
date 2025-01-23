<?php
declare(strict_types=1);

namespace Level23\Druid\InputFormats;

class TsvInputFormat extends CsvInputFormat
{
    protected ?string $delimiter;

    /**
     * @param array<string>|null $columns               Specifies the columns of the data. The columns should be in the
     *                                                  same order with the columns of your data.
     * @param string|null        $delimiter             A custom delimiter for data values.
     * @param string|null        $listDelimiter         A custom delimiter for multi-value dimensions.
     * @param bool|null          $findColumnsFromHeader If this is set, the task will find the column names from the
     *                                                  header row. Note that skipHeaderRows will be applied before
     *                                                  finding column names from the header. For example, if you set
     *                                                  skipHeaderRows to 2 and findColumnsFromHeader to true, the task
     *                                                  will skip the first two lines and then extract column
     *                                                  information from the third line. columns will be ignored if
     *                                                  this is set to true.
     * @param int                $skipHeaderRows        If this is set, the task will skip the first skipHeaderRows
     *                                                  rows.
     */
    public function __construct(
        ?array $columns = null,
        ?string $delimiter = null,
        ?string $listDelimiter = null,
        ?bool $findColumnsFromHeader = null,
        int $skipHeaderRows = 0
    ) {
        $this->delimiter = $delimiter;
        parent::__construct($columns, $listDelimiter, $findColumnsFromHeader, $skipHeaderRows);
    }

    /**
     * Return the TsvInputFormat so that it can be used in a druid query.
     *
     * @return array<string,string|string[]|bool|int>
     */
    public function toArray(): array
    {
        $result         = parent::toArray();
        $result['type'] = 'tsv';

        if ($this->delimiter !== null) {
            $result['delimiter'] = $this->delimiter;
        }

        return $result;
    }
}