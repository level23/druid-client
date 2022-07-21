<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\DataSources;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\DataSources\LookupDataSource;

class LookupDataSourceTest extends TestCase
{
    public function testLookupDataSource(): void
    {
        $dataSource = new LookupDataSource('country_names');

        $this->assertEquals([
            'type' => 'lookup',
            'name' => 'country_names',
        ], $dataSource->toArray());
    }
}