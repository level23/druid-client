<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Concerns;

use Level23\Druid\DruidClient;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Tasks\IndexTaskBuilder;
use Level23\Druid\TuningConfig\TuningConfig;

class HasTuningConfigTest extends TestCase
{
    protected IndexTaskBuilder $builder;

    public function setUp(): void
    {
        $this->builder = new IndexTaskBuilder(new DruidClient([]), 'dataSource');
    }

    /**
     * @throws \ReflectionException
     */
    public function testTuningConfig(): void
    {
        $tuning = new TuningConfig();
        $tuning->setMaxNumSubTasks(5);

        $response = $this->builder->tuningConfig($tuning);

        $this->assertEquals($this->builder, $response);

        $this->assertEquals(
            $tuning,
            $this->getProperty($this->builder, 'tuningConfig')
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testTuningConfigAsArray(): void
    {
        $tuning = new TuningConfig();
        $tuning->setMaxNumSubTasks(5);

        $response = $this->builder->tuningConfig(['maxNumSubTasks' => 5]);
        $this->assertEquals($this->builder, $response);

        $this->assertEquals(
            $tuning,
            $this->getProperty($this->builder, 'tuningConfig')
        );
    }
}
