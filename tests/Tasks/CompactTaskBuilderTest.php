<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Tasks;

use Mockery;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Hamcrest\Core\IsInstanceOf;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Tasks\CompactTask;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Context\TaskContext;
use Level23\Druid\Tasks\TaskInterface;
use Level23\Druid\Tasks\CompactTaskBuilder;
use Level23\Druid\TuningConfig\TuningConfig;

class CompactTaskBuilderTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testBuilder(): void
    {
        $client  = new DruidClient([]);
        $builder = new CompactTaskBuilder($client, 'dataSource');

        $response = $builder->targetCompactionSize(1024);

        $this->assertEquals($response, $builder);

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

    /**
     * @return array<array<array<string,int>|null|string|TuningConfig|int|TaskContext>>
     */
    public function buildTaskDataProvider(): array
    {
        $tuningConfig = new TuningConfig();
        $tuningConfig->setMaxRetry(3);

        return [
            [['priority' => 25], null, "year", $tuningConfig, 1024, "task-1337"],
            [['priority' => 25], "12-02-2019/13-02-2019", "day", null, null, null],
            [['priority' => 25], "12-02-2019/13-02-2019", "day", $tuningConfig, 1024, null],
            [['priority' => 25], "12-02-2019/13-02-2019", null, $tuningConfig, 1024, null],
            [(new TaskContext())->setPriority(10), "yesterday/now", "hour", $tuningConfig, null, null],
            [[], "12-02-2019/13-02-2019", "day", null, 1024, "task-1337"],
        ];
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param array<string,string>|TaskContext $context
     * @param string|null                      $interval
     * @param string|null                      $segmentGranularity
     * @param TuningConfig|null                $tuningConfig
     * @param int|null                         $targetCompactionSizeBytes
     * @param string|null                      $taskId
     *
     * @throws \Exception
     * @dataProvider        buildTaskDataProvider
     */
    public function testBuildTask(
        $context,
        ?string $interval,
        ?string $segmentGranularity,
        ?TuningConfig $tuningConfig,
        int $targetCompactionSizeBytes = null,
        string $taskId = null
    ): void {
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

        if ($targetCompactionSizeBytes) {
            $builder->targetCompactionSize($targetCompactionSizeBytes);
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

        if ($taskId) {
            $builder->taskId($taskId);
        }

        $mock = new Mockery\Generator\MockConfigurationBuilder();
        $mock->setInstanceMock(true);
        $mock->setName(CompactTask::class);
        $mock->addTarget(TaskInterface::class);

        Mockery::mock($mock)
            ->shouldReceive('__construct')
            ->once()
            ->with(
                $dataSource,
                new IsInstanceOf(Interval::class),
                $segmentGranularity,
                $tuningConfig,
                new IsInstanceOf(TaskContext::class),
                $targetCompactionSizeBytes,
                $taskId
            );

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $builder->shouldAllowMockingProtectedMethods()->buildTask($context);

        $this->assertInstanceOf(TaskInterface::class, $response);
    }
}
