<?php
declare(strict_types=1);

namespace Level23\Druid\DataSources;

use Level23\Druid\Queries\QueryInterface;

class QueryDataSource implements DataSourceInterface
{
    /**
     * @var \Level23\Druid\Queries\QueryInterface
     */
    protected QueryInterface $query;

    /**
     * @param \Level23\Druid\Queries\QueryInterface $query
     */
    public function __construct(QueryInterface $query)
    {
        $this->query = $query;
    }

    /**
     * Return the TableDataSource so that it can be used in a druid query.
     *
     * @return array<string,string|array<string,array<mixed>|int|string>>
     */
    public function toArray(): array
    {
        return [
            'type'  => 'query',
            'query' => $this->query->toArray(),
        ];
    }
}