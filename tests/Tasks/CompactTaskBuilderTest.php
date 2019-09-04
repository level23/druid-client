<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Tasks;

use Mockery;
use tests\TestCase;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Hamcrest\Core\IsInstanceOf;
use Level23\Druid\Tasks\CompactTask;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Context\TaskContext;
use Level23\Druid\Tasks\TaskInterface;
use Level23\Druid\Tasks\CompactTaskBuilder;
use Level23\Druid\TuningConfig\TuningConfig;

class CompactTaskBuilderTest extends TestCase
{
    public function testBuilder()
    {
        $client  = new DruidClient([]);
        $builder = new CompactTaskBuilder($client, 'dataSource');

        $builder->targetCompactionSize(1024);

        $this->assertEquals(
            $client,
            $this->getProperty($builder, 'client')
        );

        $this->assertEquals(
            'dataSource',
            $this->getProperty($builder, 'dataSource')
        );

        $this->assertEquals(
            1024,
            $this->getProperty($builder, 'targetCompactionSizeBytes')
        );
    }

    public function buildTaskDataProvider(): array
    {
        $tuningConfig = new TuningConfig();
        $tuningConfig->setMaxRetry(3);

        return [
            [['priority' => 25], null, "year", $tuningConfig],
            [['priority' => 25], "12-02-2019/13-02-2019", "day", null],
            [['priority' => 25], "12-02-2019/13-02-2019", "day", $tuningConfig],
            [['priority' => 25], "12-02-2019/13-02-2019", null, $tuningConfig],
            [(new TaskContext())->setPriority(10), "yesterday/now", "hour", $tuningConfig],
            [[], "12-02-2019/13-02-2019", "day", null],
        ];
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param array|TaskContext $context
     * @param string|null       $interval
     * @param string|null       $segmentGranularity
     * @param TuningConfig|null $tuningConfig
     *
     * @throws \Exception
     * @dataProvider        buildTaskDataProvider
     */
    public function testBuildTask($context, $interval, $segmentGranularity, $tuningConfig)
    {
        $dataSource = 'myThings';
        $client     = new DruidClient([]);

        $builder = Mockery::mock(CompactTaskBuilder::class, [$client, $dataSource]);
        $builder->makePartial();

        if (!$interval) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('You have to specify an interval!');

            /** @noinspection PhpUndefinedMethodInspection */
            $builder->shouldAllowMockingProtectedMethods()->buildTask($context);

            return;
        }

        if ($segmentGranularity) {
            $builder->segmentGranularity($segmentGranularity);
        }

        if ($tuningConfig) {
            $builder->tuningConfig($tuningConfig);
        }

        $intervalObject = new Interval($interval);

        $response = $builder->interval($interval);
        $this->assertEquals($builder, $response);

        $builder->shouldAllowMockingProtectedMethods()
            ->shouldReceive('validateInterval')
            ->once()
            ->andReturnUsing(function (string $givenDataSource, Interval $givenInterval) use (
                $dataSource,
                $intervalObject
            ) {
                $this->assertEquals($dataSource, $givenDataSource);
                $this->assertEquals($intervalObject->getInterval(), $givenInterval->getInterval());
            });

        $mock = new Mockery\Generator\MockConfigurationBuilder();
        $mock->setInstanceMock(true);
        $mock->setName(CompactTask::class);
        $mock->addTarget(TaskInterface::class);

        $task = Mockery::mock($mock)
            ->shouldReceive('__construct')
            ->once()
            ->with(
                $dataSource,
                new IsInstanceOf(Interval::class),
                $segmentGranularity,
                $tuningConfig,
                new IsInstanceOf(TaskContext::class)
            );

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $builder->shouldAllowMockingProtectedMethods()->buildTask($context);

        $this->assertInstanceOf(TaskInterface::class, $response);
    }
}