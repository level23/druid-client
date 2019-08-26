<?php
declare(strict_types=1);

namespace tests\Level23\Druid;

use Mockery;
use Exception;
use tests\TestCase;
use Hamcrest\Type\IsArray;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Tasks\IndexTask;
use GuzzleHttp\Client as GuzzleClient;
use Level23\Druid\Metadata\Structure;
use Level23\Druid\Tasks\TaskInterface;
use Level23\Druid\Queries\GroupByQuery;
use Level23\Druid\Queries\QueryBuilder;
use GuzzleHttp\Exception\ServerException;
use Level23\Druid\Tasks\IndexTaskBuilder;
use GuzzleHttp\Exception\RequestException;
use Level23\Druid\Metadata\MetadataBuilder;
use Level23\Druid\Tasks\CompactTaskBuilder;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Level23\Druid\Firehoses\IngestSegmentFirehose;
use Level23\Druid\Aggregations\AggregatorInterface;
use Level23\Druid\Exceptions\QueryResponseException;

class DruidClientTest extends TestCase
{
    /**
     * @var \Level23\Druid\DruidClient
     */
    protected $client;

    public function setUp(): void
    {
        $this->client = new DruidClient([]);
    }

    public function testInvalidGranularity()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The given granularity is invalid');

        $this->client->query('hits', 'wrong');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testQuery()
    {
        $builder = Mockery::mock('overload:' . QueryBuilder::class);
        $builder->shouldReceive('__construct')
            ->once()
            ->with($this->client, 'randomDataSource', 'quarter');

        $this->client->query('randomDataSource', 'quarter');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMetaBuilder()
    {
        $builder = Mockery::mock('overload:' . MetadataBuilder::class);
        $builder->shouldReceive('__construct')
            ->once()
            ->with($this->client);

        $this->client->metadata();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCompact()
    {
        $builder = Mockery::mock('overload:' . CompactTaskBuilder::class);
        $builder->shouldReceive('__construct')
            ->once()
            ->with($this->client, 'someDataSource');

        $this->client->compact('someDataSource');
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testReindex()
    {
        $builder = new Mockery\Generator\MockConfigurationBuilder();
        $builder->setInstanceMock(true);
        $builder->setName(MetadataBuilder::class);

        $metaDataBuilder = Mockery::mock($builder);

        $client = Mockery::mock(DruidClient::class, [[]]);
        $client->makePartial();

        $structure = new Structure('somethingElse', ['name' => 'string', 'room' => 'long'], ['salary' => 'double']);

        $metaDataBuilder->shouldReceive('structure')
            ->once()
            ->with('somethingElse')
            ->andReturn($structure);

        $indexTaskBuilder = Mockery::mock('overload:' . IndexTaskBuilder::class);
        $indexTaskBuilder->shouldReceive('__construct')
            ->once()
            ->with($client, 'somethingElse', IngestSegmentFirehose::class);

        $indexTaskBuilder->shouldReceive('dimension')
            ->with('name', 'string')
            ->once();

        $indexTaskBuilder->shouldReceive('dimension')
            ->with('room', 'long')
            ->once();

        $indexTaskBuilder->shouldReceive('sum')
            ->once()
            ->with('salary', 'salary', 'double');

        $client->shouldReceive('metadata')
            ->once()
            ->andReturn($metaDataBuilder);

        $client->reindex('somethingElse');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testExecuteTask()
    {
        $builder = new Mockery\Generator\MockConfigurationBuilder();
        $builder->setInstanceMock(true);
        $builder->setName(IndexTask::class);
        $builder->addTarget(TaskInterface::class);

        $task = Mockery::mock($builder);

        $client = Mockery::mock(DruidClient::class, [[]]);
        $client->makePartial();

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->twice();

        $client->setLogger($logger);

        $payload = ['task' => 'here'];

        $task->shouldReceive('toArray')
            ->once()
            ->andReturn($payload);

        $client->shouldAllowMockingProtectedMethods()
            ->shouldReceive('config')
            ->once()
            ->with('overlord_url')
            ->andReturn('http://overlord.test');

        $url = 'http://overlord.test/druid/indexer/v1/task';

        $client->shouldReceive('executeRawRequest')
            ->once()
            ->with('post', $url, $payload)
            ->andReturn(['task' => 'myTaskIdentifier']);

        $response = $client->executeTask($task);

        $this->assertEquals('myTaskIdentifier', $response);
    }

    /**
     * @testWith [{}, {}]
     *           [{"status": {"id":"abcd"}}, {"id":"abcd"}]
     *
     * @param array $executeRequestResponse
     * @param array $expectedResponse
     *
     * @return void
     * @throws \Exception
     */
    public function testTaskStatus(array $executeRequestResponse, array $expectedResponse)
    {
        $client = Mockery::mock(DruidClient::class, [[]]);
        $client->makePartial();

        $client->shouldAllowMockingProtectedMethods()
            ->shouldReceive('config')
            ->once()
            ->with('overlord_url')
            ->andReturn('http://overlord.test');

        $url = 'http://overlord.test/druid/indexer/v1/task/' . urlencode('abcd1234') . '/status';

        $client->shouldReceive('executeRawRequest')
            ->once()
            ->with('get', $url)
            ->andReturn($executeRequestResponse);

        $response = $client->taskStatus('abcd1234');

        $this->assertEquals($expectedResponse, $response);
    }

    public function testLogHandler()
    {
        $client = Mockery::mock(DruidClient::class, [[]]);
        $client->makePartial();

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->once();

        $client->setLogger($logger);

        /** @noinspection PhpUndefinedMethodInspection */
        $client->shouldAllowMockingProtectedMethods()->log('something');
    }

    public function executeQueryDataProvider(): array
    {
        $response = [
            'event' => [
                'name' => 'john',
                'cars' => 12,
            ],
        ];

        return [
            // Correct response
            [
                $response,
                function () use ($response) {
                    return new GuzzleResponse(200, [], (string)json_encode($response));
                },
                false,
                false,
            ],
            // Test incorrect http code
            [
                $response,
                function () {
                    throw new ServerException(
                        'Unknown exception',
                        new GuzzleRequest('GET', '/druid/v1', [], ''),
                        new GuzzleResponse(500, [], (string)json_encode([
                            'error'        => 'Unknown exception',
                            'errorMessage' => 'Some query error',
                        ]))
                    );
                },
                QueryResponseException::class,
                500,
            ],
            // Test a BadResponseException
            [
                $response,
                function () {
                    throw new BadResponseException(
                        'Bad response',
                        new GuzzleRequest('GET', '/druid/v1', [], '')
                    );
                },
                BadResponseException::class,
                0,
            ],
            // Test a RequestException
            [
                $response,
                function () {
                    throw new RequestException(
                        'Request exception',
                        new GuzzleRequest('GET', '/druid/v1', [], '')
                    );
                },
                RequestException::class,
                0,
            ],
            // Test a generic Exception
            [
                $response,
                function () {
                    throw new Exception('Something went wrong!');
                },
                Exception::class,
                0,
            ],

        ];
    }

    /**
     * @dataProvider executeQueryDataProvider
     *
     * @param array    $response
     * @param callable $responseFunction
     * @param mixed    $expectException
     * @param int      $exceptionCode
     *
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testExecuteDruidQuery(array $response, callable $responseFunction, $expectException, $exceptionCode)
    {
        $queryArray = ['something' => 'here'];

        $config = [
            'broker_url'     => 'http://test.dev',
            'guzzle_options' => ['strict' => false],
        ];

        if ($expectException) {
            $this->expectException($expectException);
            $this->expectExceptionCode($exceptionCode);
        }

        $query = Mockery::mock(GroupByQuery::class);
        $query->shouldReceive('toArray')
            ->once()
            ->andReturn($queryArray);

        $guzzle = Mockery::mock(GuzzleClient::class);
        $guzzle->shouldReceive('post')
            ->once()
            ->with(
                'http://test.dev/druid/v2',
                new IsArray()
            )
            ->andReturnUsing(function ($url, $options) use ($responseFunction, $queryArray) {
                $this->assertIsArray($options);

                $this->assertEquals(['json' => $queryArray,], $options);

                $response = call_user_func($responseFunction, $url, $options);

                return $response;
            });

        $client = Mockery::mock(DruidClient::class, [$config, $guzzle]);
        $client->makePartial();

        $client->shouldAllowMockingProtectedMethods();

        $druidResult = $client->executeQuery($query);

        if (!$expectException) {
            $this->assertEquals($response, $druidResult);
        }
    }
}