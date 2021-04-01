<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Concerns;

use Level23\Druid\DruidClient;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\VirtualColumns\VirtualColumn;

class HasVirtualColumnsTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testVirtualColumns(): void
    {
        $builder  = new QueryBuilder(new DruidClient([]), 'dataSource');
        $response = $builder->virtualColumn('concat(foo, bar)', 'fooBar');

        $this->assertEquals($builder, $response);

        $this->assertEquals([
            new VirtualColumn('concat(foo, bar)', 'fooBar', 'string'),
        ], $this->getProperty($builder, 'virtualColumns'));
    }
}
