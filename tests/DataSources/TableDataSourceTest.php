<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\DataSources;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\DataSources\TableDataSource;

class TableDataSourceTest extends TestCase
{
    public function testInlineDataSource(): void
    {
        $dataSource = new TableDataSource('people');

        $this->assertEquals([
            'type' => 'table',
            'name' => 'people',
        ], $dataSource->toArray());
    }
}