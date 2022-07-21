<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\DataSources;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\DataSources\UnionDataSource;

class UnionDataSourceTest extends TestCase
{
    public function testUnionDataSource(): void
    {
        $tableNames = ['foo', 'bar', 'baz'];
        $dataSource = new UnionDataSource($tableNames);

        $this->assertEquals([
            'type'        => 'union',
            'dataSources' => $tableNames,
        ], $dataSource->toArray());
    }
}