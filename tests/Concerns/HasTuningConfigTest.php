<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Concerns;

use tests\TestCase;
use Level23\Druid\DruidClient;
use Level23\Druid\Tasks\IndexTaskBuilder;
use Level23\Druid\TuningConfig\TuningConfig;

class HasTuningConfigTest extends TestCase
{
    /**
     * @var IndexTaskBuilder
     */
    protected $builder;

    public function setUp(): void
    {
        $this->builder = new IndexTaskBuilder(new DruidClient([]), 'dataSource');
    }

    /**
     * @throws \ReflectionException
     */
    public function testTuningConfig()
    {
        $tuning = new TuningConfig();
        $tuning->setMaxNumSubTasks(5);

        $this->builder->tuningConfig($tuning);

        $this->assertEquals(
            $tuning,
            $this->getProperty($this->builder, 'tuningConfig')
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function testTuningConfigAsArray()
    {
        $tuning = new TuningConfig();
        $tuning->setMaxNumSubTasks(5);

        $this->builder->tuningConfig(['maxNumSubTasks' => 5]);

        $this->assertEquals(
            $tuning,
            $this->getProperty($this->builder, 'tuningConfig')
        );
    }
}