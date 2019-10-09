<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Tasks;

use Mockery;
use tests\TestCase;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Tasks\CompactTask;
use Level23\Druid\Interval\Interval;
use GuzzleHttp\Client as GuzzleClient;
use Level23\Druid\Tasks\CompactTaskBuilder;
use Level23\Druid\Metadata\MetadataBuilder;

class TaskBuilderTest extends TestCase
{
    /**
     * @var \Level23\Druid\DruidClient|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $client;

    protected function setUp(): void
    {
        $guzzle = new GuzzleClient(['base_uri' => 'http://httpbin.org']);

        $this->client = Mockery::mock(DruidClient::class, [[], $guzzle]);

        parent::setUp();
    }

    public function testExecute()
    {
        $builder = Mockery::mock(CompactTaskBuilder::class, [$this->client, 'animals']);
        $builder->makePartial();

        $task = new CompactTask('animals', new Interval('12-02-2019', '13-02-2019'));

        $builder
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('buildTask')
            ->once()
            ->with([])
            ->andReturn($task);

        $this->client->shouldReceive('executeTask')
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
        $builder = Mockery::mock(CompactTaskBuilder::class, [$this->client, 'animals']);
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
        $builder = Mockery::mock(CompactTaskBuilder::class, [$this->client, 'animals']);
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
                true,
            ],
            [
                "2019-08-19T13:00:00.000Z/2019-08-19T16:00:00.000Z",
                [
                    "2019-08-19T15:00:00.000Z/2019-08-19T16:00:00.000Z" => ["size" => 75208, "count" => 4],
                    "2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z" => ["size" => 75208, "count" => 4],
                    "2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z" => ["size" => 161870, "count" => 8],
                ],
                true,
            ],
            [
                "2019-08-19T15:30:00.000Z/2019-08-19T16:00:00.000Z",
                [
                    "2019-08-19T15:10:00.000Z/2019-08-19T16:00:00.000Z" => ["size" => 75208, "count" => 4],
                    "2019-08-19T15:00:00.000Z/2019-08-19T16:00:00.000Z" => ["size" => 75208, "count" => 4],
                    "2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z" => ["size" => 75208, "count" => 4],
                    "2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z" => ["size" => 161870, "count" => 8],
                ],
                false,
            ],
            [
                "2019-08-19T13:00:00.000Z/2019-08-19T13:30:00.000Z",
                [
                    "2019-08-19T15:00:00.000Z/2019-08-19T16:00:00.000Z" => ["size" => 75208, "count" => 4],
                    "2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z" => ["size" => 75208, "count" => 4],
                    "2019-08-19T13:00:00.000Z/2019-08-19T14:30:00.000Z" => ["size" => 161870, "count" => 8],
                    "2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z" => ["size" => 161870, "count" => 8],
                ],
                false,
            ],
            [
                "2011-04-19T00:00:00.000Z/2011-04-19T00:00:00.000Z",
                [
                    "2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z" => ["size" => 75208, "count" => 4],
                    "2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z" => ["size" => 161870, "count" => 8],
                ],
                false,
            ],
        ];
    }

    /**
     * @param string $givenInterval
     * @param array  $allIntervals
     *
     * @param bool   $expectsValid
     *
     * @throws \Exception
     * @dataProvider validateIntervalDataProvider
     */
    public function testValidateInterval(string $givenInterval, array $allIntervals, bool $expectsValid)
    {
        $dataSource = 'animals';

        $builder = Mockery::mock(CompactTaskBuilder::class, [$this->client, $dataSource]);
        $builder->makePartial();

        $metadata = Mockery::mock(MetadataBuilder::class, [$this->client]);

        $metadata->shouldReceive('intervals')
            ->once()
            ->with($dataSource)
            ->andReturn($allIntervals);

        $this->client->shouldReceive('metadata')
            ->once()
            ->andReturn($metadata);

        if (!$expectsValid) {
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