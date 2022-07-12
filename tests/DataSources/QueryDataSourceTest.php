<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\DataSources;

use Level23\Druid\DruidClient;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\DataSources\QueryDataSource;

class QueryDataSourceTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testQueryDataSource(): void
    {
        $query = new QueryBuilder(new DruidClient([]), 'test');
        $query->interval('now - 1 day', 'now');

        $dataSource = new QueryDataSource($query->getQuery());

        $this->assertEquals([
            'type'  => 'query',
            'query' => $query->toArray(),
        ], $dataSource->toArray());
    }
}