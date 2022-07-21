<?php
declare(strict_types=1);

namespace Level23\Druid\DataSources;

class TableDataSource implements DataSourceInterface
{
    public string $dataSourceName;

    public function __construct(string $dataSourceName)
    {
        $this->dataSourceName = $dataSourceName;
    }

    /**
     * Return the TableDataSource so that it can be used in a druid query.
     *
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'type' => 'table',
            'name' => $this->dataSourceName,
        ];
    }
}