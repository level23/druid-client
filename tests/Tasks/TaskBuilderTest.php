<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Tasks;

use Mockery;
use tests\TestCase;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Tasks\CompactTask;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Tasks\CompactTaskBuilder;
use Level23\Druid\Metadata\MetadataBuilder;

class TaskBuilderTest extends TestCase
{
    public function testExecute()
    {
        $client = Mockery::mock(DruidClient::class, [[]]);

        $builder = Mockery::mock(CompactTaskBuilder::class, [$client, 'animals']);
        $builder->makePartial();

        $task = new CompactTask('animals', new Interval('12-02-2019', '13-02-2019'));

        $builder
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('buildTask')
            ->once()
            ->with([])
            ->andReturn($task);

        $client->shouldReceive('executeTask')
            ->once()
            ->with($task)
            ->andReturn('myTaskId');

        $response = $builder->execute([]);

        $this->assertEquals('myTaskId', $response);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testToJson()
    {
        $client = Mockery::mock(DruidClient::class, [[]]);

        $builder = Mockery::mock(CompactTaskBuilder::class, [$client, 'animals']);
        $builder->makePartial();

        $task = Mockery::mock(CompactTask::class, ['animals', new Interval('12-02-2019', '13-02-2019')]);

        $task->shouldReceive('toArray')
            ->andReturn(['task' => 'here']);

        $builder
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('buildTask')
            ->once()
            ->with(['context' => 'here'])
            ->andReturn($task);

        $response = $builder->toJson(['context' => 'here']);

        $this->assertEquals(json_encode(['task' => 'here'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), $response);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testToArray()
    {
        $client = Mockery::mock(DruidClient::class, [[]]);

        $builder = Mockery::mock(CompactTaskBuilder::class, [$client, 'animals']);
        $builder->makePartial();

        $task = Mockery::mock(CompactTask::class, ['animals', new Interval('12-02-2019', '13-02-2019')]);

        $task->shouldReceive('toArray')
            ->andReturn(['task' => 'here']);

        $builder
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('buildTask')
            ->once()
            ->with(['context' => 'here'])
            ->andReturn($task);

        $response = $builder->toArray(['context' => 'here']);

        $this->assertEquals(['task' => 'here'], $response);
    }

    public function validateIntervalDataProvider(): array
    {
        return [
            [
                "2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z",
                [
                    "2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z" => ["size" => 75208, "count" => 4],
                    "2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z" => ["size" => 161870, "count" => 8],
                ],
            ],
            [
                "2011-04-19T00:00:00.000Z/2011-04-19T00:00:00.000Z",
                [
                    "2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z" => ["size" => 75208, "count" => 4],
                    "2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z" => ["size" => 161870, "count" => 8],
                ],
            ],
        ];
    }

    /**
     * @param string $givenInterval
     * @param array  $allIntervals
     *
     * @throws \Exception
     * @dataProvider validateIntervalDataProvider
     */
    public function testValidateInterval(string $givenInterval, array $allIntervals)
    {
        $dataSource = 'animals';

        $client = Mockery::mock(DruidClient::class, [[]]);

        $builder = Mockery::mock(CompactTaskBuilder::class, [$client, $dataSource]);
        $builder->makePartial();

        $metadata = Mockery::mock(MetadataBuilder::class, [$client]);

        $metadata->shouldReceive('intervals')
            ->once()
            ->with($dataSource)
            ->andReturn($allIntervals);

        $client->shouldReceive('metadata')
            ->once()
            ->andReturn($metadata);

        if (!array_key_exists($givenInterval, $allIntervals)) {
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage('Error, invalid interval given.');
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $builder->shouldAllowMockingProtectedMethods()->validateInterval(
            $dataSource,
            new Interval($givenInterval)
        );
    }
}