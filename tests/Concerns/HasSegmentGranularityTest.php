<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Concerns;

use Level23\Druid\DruidClient;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Tasks\IndexTaskBuilder;

class HasSegmentGranularityTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testSegmentGranularity(): void
    {
        $builder = new IndexTaskBuilder(new DruidClient([]), 'dataSource');

        $result = $builder->segmentGranularity('year');

        $this->assertEquals($builder, $result);

        $this->assertEquals(
            'year',
            $this->getProperty($builder, 'segmentGranularity')
        );
    }
}
