<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Concerns;

use tests\TestCase;
use Level23\Druid\DruidClient;
use Level23\Druid\Tasks\IndexTaskBuilder;

class HasSegmentGranularityTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testSegmentGranularity()
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
