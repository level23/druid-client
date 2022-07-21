<?php
declare(strict_types=1);

namespace Level23\Druid\DataSources;

class InlineDataSource implements DataSourceInterface
{
    /**
     * @var string[]
     */
    protected array $columnNames;

    /**
     * @var array<scalar[]>
     */
    protected array $rows;

    /**
     * @param string[]        $columnNames
     * @param array<scalar[]> $rows
     */
    public function __construct(array $columnNames, array $rows)
    {
        $this->columnNames = $columnNames;
        $this->rows        = $rows;
    }

    /**
     * Return the TableDataSource so that it can be used in a druid query.
     *
     * @return array<string,array<array<scalar>|string>|string>
     */
    public function toArray(): array
    {
        return [
            'type'        => 'inline',
            'columnNames' => $this->columnNames,
            'rows'        => $this->rows,
        ];
    }
}