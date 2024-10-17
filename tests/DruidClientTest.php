<?php
declare(strict_types=1);

namespace Level23\Druid\Tests;

use Mockery;
use ValueError;
use GuzzleHttp\Client;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Psr7\Response;
use Level23\Druid\DruidClient;
use Mockery\LegacyMockInterface;
use Level23\Druid\Tasks\IndexTask;
use GuzzleHttp\Client as GuzzleClient;
use Level23\Druid\Metadata\Structure;
use Level23\Druid\Tasks\TaskInterface;
use Level23\Druid\Queries\QueryBuilder;
use Psr\Http\Message\ResponseInterface;
use Level23\Druid\Tasks\KillTaskBuilder;
use Level23\Druid\Lookups\LookupBuilder;
use Level23\Druid\Tasks\IndexTaskBuilder;
use Level23\Druid\Queries\QueryInterface;
use GuzzleHttp\Exception\ServerException;
use Level23\Druid\Responses\TaskResponse;
use GuzzleHttp\Exception\RequestException;
use Level23\Druid\Metadata\MetadataBuilder;
use Level23\Druid\Tasks\CompactTaskBuilder;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Exception\BadResponseException;
use Level23\Druid\Queries\SegmentMetadataQuery;
use Level23\Druid\InputSources\DruidInputSource;
use Level23\Druid\InputSources\LocalInputSource;
use Level23\Druid\Exceptions\QueryResponseException;

class DruidClientTest extends TestCase
{
    /**
     * @param \GuzzleHttp\Client|null $guzzle
     *
     * @return \Mockery\LegacyMockInterface|\Mockery\MockInterface|\Level23\Druid\DruidClient
     */
    protected function mockDruidClient(GuzzleClient $guzzle = null): LegacyMockInterface|MockInterface|DruidClient
    {
        $guzzle = $guzzle ?: new GuzzleClient(['base_uri' => 'https://httpbin.org']);

        return Mockery::mock(DruidClient::class, [['retries' => 0], $guzzle]);
    }

    public function testInvalidGranularity(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('"wrong" is not a valid backing value for enum Level23\Druid\Types\Granularity');

        $client = new DruidClient([]);
        $client->query('hits', 'wrong');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMakeGuzzleClient(): void
    {
        Mockery::mock('overload:' . GuzzleClient::class)
            ->shouldReceive('__construct')
            ->once()
            ->with([
                'timeout'         => 16,
                'connect_timeout' => 5,
                'headers'         => [
                    'User-Agent' => 'level23 druid client package',
                ],
            ]);

        new DruidClient(['timeout' => 16, 'connect_timeout' => 5]);
    }

    /**
     * @throws \ReflectionException
     */
    public function testSetGuzzleClient(): void
    {
        $guzzle = new GuzzleClient(['base_uri' => 'https://httpbin.org']);

        $client = new DruidClient([]);
        $client->setGuzzleClient($guzzle);

        $this->assertEquals($guzzle,
            $this->getProperty($client, 'client')
        );
    }

    public function testAuth(): void
    {
        $guzzle = Mockery::mock(Client::class);
        $client = new DruidClient([], $guzzle);

        // Set the authentication credentials.
        $client->auth('foo', 'bar');

        $guzzle->shouldReceive('get')
            ->once()
            ->with('/druid/coordinator/v1/servers?simple', ['query' => [], 'auth' => ['foo', 'bar']])
            ->andReturn(new Response(200, [], '{ "status" : "OK" }'));

        $client->executeRawRequest('GET', '/druid/coordinator/v1/servers?simple');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testQuery(): void
    {
        $client = new DruidClient([]);

        $builder = Mockery::mock('overload:' . QueryBuilder::class);
        $builder->shouldReceive('__construct')
            ->once()
            ->with($client, 'randomDataSource', 'quarter');

        $client->query('randomDataSource', 'quarter');
    }

    public function testLookup(): void
    {
        $client = new DruidClient([]);

        $instance = $client->lookup();

        $this->assertInstanceOf(LookupBuilder::class, $instance);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMetaBuilder(): void
    {
        $client = new DruidClient([]);

        $builder = Mockery::mock('overload:' . MetadataBuilder::class);
        $builder->shouldReceive('__construct')
            ->once()
            ->with($client);

        $client->metadata();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testIndex(): void
    {
        $client = new DruidClient([]);

        $inputSource = new LocalInputSource(['/path/to/file.json']);

        $builder = Mockery::mock('overload:' . IndexTaskBuilder::class);
        $builder->shouldReceive('__construct')
            ->once()
            ->with($client, 'someDataSource', $inputSource);

        $client->index('someDataSource', $inputSource);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCompact(): void
    {
        $client = new DruidClient([]);

        $dataSource = 'wikipedia';

        $builder = Mockery::mock('overload:' . CompactTaskBuilder::class);
        $builder->shouldReceive('__construct')
            ->once()
            ->with($client, $dataSource);

        $client->compact($dataSource);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testKill(): void
    {
        $client = new DruidClient([]);

        $builder = Mockery::mock('overload:' . KillTaskBuilder::class);
        $builder->shouldReceive('__construct')
            ->once()
            ->with($client, 'someDataSource');

        $client->kill('someDataSource');
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException|\GuzzleHttp\Exception\GuzzleException
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testReindex(): void
    {
        $dataSource = 'somethingElse';

        $builder = new Mockery\Generator\MockConfigurationBuilder();
        $builder->setInstanceMock(true);
        $builder->setName(MetadataBuilder::class);

        $metaDataBuilder = Mockery::mock($builder);

        $client = $this->mockDruidClient();
        $client->makePartial();

        $structure = new Structure($dataSource, ['name' => 'string', 'room' => 'long'], ['salary' => 'double']);

        $metaDataBuilder->shouldReceive('structure')
            ->once()
            ->with($dataSource)
            ->andReturn($structure);

        $indexTaskBuilder = Mockery::mock('overload:' . IndexTaskBuilder::class);
        $indexTaskBuilder->shouldReceive('__construct')
            ->once()
            ->with($client, $dataSource, DruidInputSource::class);

        $indexTaskBuilder->shouldReceive('timestamp')
            ->once()
            ->with('__time', 'auto')
            ->andReturn($indexTaskBuilder);

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

        $client->reindex($dataSource);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws \Level23\Druid\Exceptions\QueryResponseException|\GuzzleHttp\Exception\GuzzleException
     */
    public function testExecuteTask(): void
    {
        $builder = new Mockery\Generator\MockConfigurationBuilder();
        $builder->setInstanceMock(true);
        $builder->setName(IndexTask::class);
        $builder->addTarget(TaskInterface::class);

        /** @var \Mockery\MockInterface|\Mockery\LegacyMockInterface|IndexTask $task */
        $task = Mockery::mock($builder);

        $client = $this->mockDruidClient();
        $client->makePartial();

        $this->assertNull($client->getLogger());

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('debug')->twice();

        $client->setLogger($logger);

        // Test that our getter also works.
        $this->assertEquals($logger, $client->getLogger());

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
     * @param array<string,array<string,string>> $executeRequestResponse
     * @param array<string,string>               $expectedResponse
     *
     * @return void
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function testTaskStatus(array $executeRequestResponse, array $expectedResponse): void
    {
        $client = $this->mockDruidClient();
        $client->makePartial();

        $client->shouldAllowMockingProtectedMethods()
            ->shouldReceive('config')
            ->once()
            ->with('overlord_url')
            ->andReturn('https://overlord.test');

        $url = 'https://overlord.test/druid/indexer/v1/task/' . urlencode('abcd1234') . '/status';

        $client->shouldReceive('executeRawRequest')
            ->once()
            ->with('get', $url)
            ->andReturn($executeRequestResponse);

        $response = $client->taskStatus('abcd1234');

        $this->assertInstanceOf(TaskResponse::class, $response);
        $this->assertEquals($expectedResponse['id'] ?? '', $response->getId());
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testPollTaskStatus(): void
    {
        $client = $this->mockDruidClient();
        $client->makePartial();

        $client->shouldReceive('taskStatus')
            ->with('task-1234')
            ->andReturn(
                new TaskResponse(['status' => ['status' => 'RUNNING']]),
                new TaskResponse(['status' => ['status' => 'SUCCESS']])
            );

        $client->shouldReceive('config')
            ->with('polling_sleep_seconds')
            ->andReturn(0);

        $response = $client->pollTaskStatus('task-1234');

        $this->assertInstanceOf(TaskResponse::class, $response);

        $this->assertEquals('SUCCESS', $response->getStatus());
    }

    public function testLogHandler(): void
    {
        $client = $this->mockDruidClient();
        $client->makePartial();

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('debug')->once();

        $client->setLogger($logger);

        /** @noinspection PhpUndefinedMethodInspection */
        $client->shouldAllowMockingProtectedMethods()->log('something');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws \Level23\Druid\Exceptions\QueryResponseException|\GuzzleHttp\Exception\GuzzleException
     */
    public function testExecuteDruidQuery(): void
    {
        $client = $this->mockDruidClient();
        $client->makePartial();

        $builder = new Mockery\Generator\MockConfigurationBuilder();
        $builder->setInstanceMock(true);
        $builder->setName(SegmentMetadataQuery::class);
        $builder->addTarget(QueryInterface::class);

        /**
         * @var \Mockery\MockInterface|\Mockery\LegacyMockInterface|SegmentMetadataQuery|QueryInterface $query
         */
        $query = Mockery::mock($builder);

        $query->shouldReceive('toArray')
            ->once()
            ->andReturn(['query' => 'here']);

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('debug')->twice();

        $client->shouldReceive('config')
            ->with('broker_url')
            ->once()
            ->andReturn('http://broker.url');

        $client->shouldReceive('executeRawRequest')
            ->once()
            ->with('post', 'http://broker.url/druid/v2', ['query' => 'here'])
            ->andReturn(['result' => 'yes']);

        $client->setLogger($logger);

        $this->assertEquals(['result' => 'yes'], $client->executeQuery($query));
    }

    public function testParseResponse(): void
    {
        $client = $this->mockDruidClient();
        $client->makePartial();

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('debug')->times(3);

        $client->setLogger($logger);

        $this->expectException(QueryResponseException::class);
        $this->expectExceptionMessage('Failed to parse druid response. Invalid json?');

        /** @noinspection PhpUndefinedMethodInspection */
        $client->shouldAllowMockingProtectedMethods()
            ->parseResponse(new GuzzleResponse(200, [], 'something'));
    }

    public function testConfig(): void
    {
        $routerUrl = 'https://router.url.here';
        $client    = new DruidClient(['pieter' => 'okay', 'router_url' => $routerUrl]);

        $this->assertEquals('okay', $client->config('pieter'));
        $this->assertEquals('bar', $client->config('foo', 'bar'));

        // test a default config param
        $this->assertEquals(500, $client->config('retry_delay_ms'));

        $this->assertEquals($routerUrl, $client->config('overlord_url'));
        $this->assertEquals($routerUrl, $client->config('coordinator_url'));
        $this->assertEquals($routerUrl, $client->config('broker_url'));
    }

    /**
     * @return array<array<string|bool|\Closure>>
     */
    public static function executeRawRequestDataProvider(): array
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
                'post',
                function () use ($response) {
                    return new GuzzleResponse(200, [], (string)json_encode($response));
                },
                false,
            ],
            // Correct response
            [
                'GET',
                function () use ($response) {
                    return new GuzzleResponse(200, [], (string)json_encode($response));
                },
                false,
            ],
            // Correct response
            [
                'GET',
                function () {
                    return new GuzzleResponse(204, [], '');
                },
                false,
                true,
            ],
            // Test exception
            [
                "PoSt",
                function () {
                    throw new ServerException(
                        'Unknown exception',
                        new GuzzleRequest('GET', '/druid/v1', [], ''),
                        new GuzzleResponse(500, [], (string)json_encode([
                            'blaat' => 'woei',
                        ]))
                    );
                },
                ServerException::class,
            ],
            // Test incorrect http code
            [
                'PoSt',
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
            ],
            // Test incorrect http code
            [
                'PoSt',
                function () {
                    throw new ServerException(
                        'Unknown exception',
                        new GuzzleRequest('GET', '/druid/v1', [], ''),
                        new GuzzleResponse(500, [], (string)json_encode([
                            'blaat' => 'woei',
                        ]))
                    );
                },
                ServerException::class,
            ],
            // Test a BadResponseException
            [
                'GET',
                function () {
                    throw new BadResponseException(
                        'Bad response',
                        new GuzzleRequest('GET', '/druid/v1', [], ''),
                        new GuzzleResponse(500)
                    );
                },
                BadResponseException::class,
            ],
            // Test a RequestException
            [
                'POST',
                function () {
                    throw new RequestException(
                        'Request exception',
                        new GuzzleRequest('GET', '/druid/v1', [], '')
                    );
                },
                RequestException::class,
            ],
            // Test a RequestException
            [
                'POST',
                function () {
                    throw new ServerException(
                        'Request exception',
                        new GuzzleRequest('GET', '/druid/v1', [], ''),
                        new GuzzleResponse(
                            502,
                            [],
                            "<title>Error 502 Bad Gateway</title>"
                        )
                    );
                },
                QueryResponseException::class,
            ],
        ];
    }

    /**
     * @dataProvider executeRawRequestDataProvider
     *
     * @param string      $method
     * @param callable    $responseFunction
     * @param bool|string $expectException
     * @param bool        $is204
     *
     * @throws \Level23\Druid\Exceptions\QueryResponseException|\GuzzleHttp\Exception\GuzzleException
     */
    public function testExecuteRawRequest(
        string $method,
        callable $responseFunction,
        bool|string $expectException,
        bool $is204 = false
    ): void {
        if ($expectException && is_string($expectException)) {
            $this->expectException($expectException);
        }

        $url  = 'https://test.dev/v2/task';
        $data = ['payload' => 'here'];

        /** @var GuzzleClient|\Mockery\LegacyMockInterface|\Mockery\MockInterface $guzzle */
        $guzzle = Mockery::mock(GuzzleClient::class);
        $guzzle->shouldReceive(strtolower($method))
            ->once()
            ->with($url, (strtolower($method) == 'post' ? ['json' => $data] : ['query' => $data]))
            ->andReturnUsing($responseFunction);

        $client = $this->mockDruidClient($guzzle);
        $client->makePartial();

        $expectedResponse = [];
        if (!$is204 && !$expectException) {

            $client->shouldAllowMockingProtectedMethods()
                ->shouldReceive('parseResponse')
                ->once()
                ->andReturnUsing(function (ResponseInterface $input) use (&$expectedResponse) {
                    return $expectedResponse = json_decode($input->getBody()->getContents(), true) ?: [];
                });
        }

        $response = $client->executeRawRequest($method, $url, $data);

        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCancelQuery(): void
    {
        $guzzle = new GuzzleClient(['base_uri' => 'https://httpbin.org']);

        $client = Mockery::mock(DruidClient::class, [
            [],
            $guzzle,
        ]);
        $client->makePartial();

        $guzzle = Mockery::mock(GuzzleClient::class);
        $guzzle->shouldReceive('delete')
            ->once()
            ->with('/druid/v2/my-long-query-id', [])
            ->times(1)
            ->andReturnUsing(function () {
                return new GuzzleResponse(202, [], '');
            });

        $client->setGuzzleClient($guzzle);
        $client->cancelQuery('my-long-query-id');
    }

    /**
     * @testWith [2, 10]
     *           [0, 200]
     *           [1, 1000]
     *           [5, 500]
     *           [5, 0]
     *
     * @param int $retries
     * @param int $delay
     *
     * @throws \Level23\Druid\Exceptions\QueryResponseException|\GuzzleHttp\Exception\GuzzleException
     */
    public function testExecuteRawRequestWithRetries(int $retries, int $delay): void
    {
        $guzzle = new GuzzleClient(['base_uri' => 'https://httpbin.org']);

        $client = Mockery::mock(DruidClient::class, [
            ['retries' => $retries, 'retry_delay_ms' => $delay],
            $guzzle,
        ]);
        $client->makePartial();

        $url  = 'https://test.dev/v2/task';
        $data = ['payload' => 'here'];

        $guzzle = Mockery::mock(GuzzleClient::class);
        $guzzle->shouldReceive('get')
            ->once()
            ->with($url, ['query' => $data])
            ->times(($retries + 1))
            ->andReturnUsing(function () {
                throw new ServerException(
                    'Request exception',
                    new GuzzleRequest('GET', '/druid/v1', [], ''),
                    new GuzzleResponse(
                        502,
                        [],
                        "<title>Error 502 Bad Gateway</title>"
                    )
                );
            });

        if ($delay > 0) {
            $client->shouldAllowMockingProtectedMethods()
                ->shouldReceive('usleep')
                ->with(($delay * 1000))
                ->times($retries);
        } else {
            $client->shouldAllowMockingProtectedMethods()
                ->shouldNotReceive('usleep');
        }

        $client->shouldReceive('config')
            ->with('retry_delay_ms', 500)
            ->times($retries + 1)
            ->andReturn($delay);

        $client->shouldReceive('config')
            ->with('retries', 2)
            ->times($retries + 1)
            ->andReturn($retries);

        $client->setGuzzleClient($guzzle);

        $this->expectException(QueryResponseException::class);
        $this->expectExceptionMessage('We failed to execute druid query due to a 502 Bad Gateway response.');

        $client->executeRawRequest('get', $url, $data);
    }
}