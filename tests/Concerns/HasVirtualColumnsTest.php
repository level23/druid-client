<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Concerns;

use tests\TestCase;
use Level23\Druid\DruidClient;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\VirtualColumns\VirtualColumn;

class HasVirtualColumnsTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testVirtualColumns()
    {
        $builder = new QueryBuilder(new DruidClient([]), 'dataSource');
        $builder->virtualColumn('concat(foo, bar)', 'fooBar');

        $this->assertEquals([
            new VirtualColumn('concat(foo, bar)', 'fooBar', 'string'),
        ], $this->getProperty($builder, 'virtualColumns'));
    }
}