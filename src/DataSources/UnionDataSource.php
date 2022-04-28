<?php
declare(strict_types=1);

namespace Level23\Druid\DataSources;

class UnionDataSource implements DataSourceInterface
{
    /**
     * @var string[]
     */
    protected array $tableNames;

    /**
     * @param string[] $tableNames
     */
    public function __construct(array $tableNames)
    {
        $this->tableNames = $tableNames;
    }

    /**
     * Return the LookupDataSource so that it can be used in a druid query.
     *
     * @return array<string,string|string[]>
     */
    public function toArray(): array
    {
        return [
            'type'        => 'union',
            'dataSources' => $this->tableNames,
        ];
    }
}