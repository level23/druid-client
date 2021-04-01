<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Concerns;

use Level23\Druid\DruidClient;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Tasks\IndexTaskBuilder;

class HasQueryGranularityTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testQueryGranularity(): void
    {
        $builder = new IndexTaskBuilder(new DruidClient([]), 'dataSource');

        $result = $builder->queryGranularity('week');

        $this->assertEquals($builder, $result);

        $this->assertEquals(
            'week',
            $this->getProperty($builder, 'queryGranularity')
        );
    }
}
