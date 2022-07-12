<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\DataSources;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\DataSources\InlineDataSource;

class InlineDataSourceTest extends TestCase
{
    public function testInlineDataSource(): void
    {
        $columns = ['name', 'age'];
        $rows    = [
            ['John', 37],
            ['Doe', 19],
            ['Jane', 52],
        ];

        $dataSource = new InlineDataSource($columns, $rows);

        $this->assertEquals([
            'type'        => 'inline',
            'columnNames' => $columns,
            'rows'        => $rows,
        ],
            $dataSource->toArray()
        );
    }
}