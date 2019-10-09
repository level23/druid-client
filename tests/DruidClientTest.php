<?php
declare(strict_types=1);

namespace tests\Level23\Druid;

use Mockery;
use tests\TestCase;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Tasks\IndexTask;
use GuzzleHttp\Client as GuzzleClient;
use Level23\Druid\Metadata\Structure;
use Level23\Druid\Tasks\TaskInterface;
use Level23\Druid\Queries\QueryBuilder;
use Psr\Http\Message\ResponseInterface;
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
use Level23\Druid\Firehoses\IngestSegmentFirehose;
use Level23\Druid\Exceptions\QueryResponseException;

class DruidClientTest extends TestCase
{
    /**
     * @param array                   $config
     * @param \GuzzleHttp\Client|null $guzzle
     *
     * @return \Level23\Druid\DruidClient|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected function mockDruidClient(array $config = [], GuzzleClient $guzzle = null)
    {
        $guzzle = $guzzle ?: new GuzzleClient(['base_uri' => 'http://httpbin.org']);

        return Mockery::mock(DruidClient::class, [['retries' => 0], $guzzle]);
    }

    public function testInvalidGranularity()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The given granularity is invalid');

        $client = new DruidClient([]);
        $client->query('hits', 'wrong');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMakeGuzzleClient()
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
    public function testSetGuzzleClient()
    {
        $guzzle = new GuzzleClient(['base_uri' => 'http://httpbin.org']);

        $client = new DruidClient([]);
        $client->setGuzzleClient($guzzle);

        $this->assertEquals($guzzle,
            $this->getProperty($client, 'client')
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testQuery()
    {
        $client = new DruidClient([]);

        $builder = Mockery::mock('overload:' . QueryBuilder::class);
        $builder->shouldReceive('__construct')
            ->once()
            ->with($client, 'randomDataSource', 'quarter');

        $client->query('randomDataSource', 'quarter');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testMetaBuilder()
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
    public function testCompact()
    {
        $client = new DruidClient([]);

        $builder = Mockery::mock('overload:' . CompactTaskBuilder::class);
        $builder->shouldReceive('__construct')
            ->once()
            ->with($client, 'someDataSource');

        $client->compact('someDataSource');
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testReindex()
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
            ->with($client, $dataSource, IngestSegmentFirehose::class);

        $indexTaskBuilder->shouldReceive('setFromDataSource')
            ->with($dataSource)
            ->once();

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
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testExecuteTask()
    {
        $builder = new Mockery\Generator\MockConfigurationBuilder();
        $builder->setInstanceMock(true);
        $builder->setName(IndexTask::class);
        $builder->addTarget(TaskInterface::class);

        /** @var \Mockery\MockInterface|\Mockery\LegacyMockInterface|IndexTask $task */
        $task = Mockery::mock($builder);

        $client = $this->mockDruidClient();
        $client->makePartial();

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('debug')->twice();

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
        $client = $this->mockDruidClient();
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

        $this->assertInstanceOf(TaskResponse::class, $response);
        $this->assertEquals($expectedResponse['id'] ?? '', $response->getId());
    }

    public function testLogHandler()
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
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testExecuteDruidQuery()
    {
        $client = $this->mockDruidClient();
        $client->makePartial();;

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

    public function testParseResponse()
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

    public function testConfig()
    {
        $routerUrl = 'http://router.url.here';
        $client    = new DruidClient(['pieter' => 'okay', 'router_url' => $routerUrl]);

        $this->assertEquals('okay', $client->config('pieter'));
        $this->assertEquals('bar', $client->config('foo', 'bar'));

        // test a default config param
        $this->assertEquals(500, $client->config('retry_delay_ms'));

        $this->assertEquals($routerUrl, $client->config('overlord_url'));
        $this->assertEquals($routerUrl, $client->config('coordinator_url'));
        $this->assertEquals($routerUrl, $client->config('broker_url'));
    }

    public function executeRawRequestDataProvider(): array
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
                        null
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
                        new GuzzleRequest('GET', '/druid/v1', [], '')
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
     * @param string   $method
     * @param callable $responseFunction
     * @param mixed    $expectException
     * @param bool     $is204
     *
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testExecuteRawRequest(
        string $method,
        callable $responseFunction,
        $expectException,
        $is204 = false
    ) {
        if ($expectException) {
            $this->expectException($expectException);
        }


        $url  = 'http://test.dev/v2/task';
        $data = ['payload' => 'here'];

        /** @var GuzzleClient|\Mockery\LegacyMockInterface|\Mockery\MockInterface $guzzle */
        $guzzle = Mockery::mock(GuzzleClient::class);
        $guzzle->shouldReceive(strtolower($method))
            ->once()
            ->with($url, (strtolower($method) == 'post' ? ['json' => $data] : ['query' => $data]))
            ->andReturnUsing($responseFunction);

        $client = $this->mockDruidClient([], $guzzle);
        $client->makePartial();


        $expectedResponse = [];
        if (!$is204 && (!$expectException || $expectException instanceof ResponseInterface)) {

            $client->shouldAllowMockingProtectedMethods()
                ->shouldReceive('parseResponse')
                ->once()
                ->andReturnUsing(function (ResponseInterface $input) use (&$expectedResponse) {
                    $response         = \GuzzleHttp\json_decode($input->getBody()->getContents(), true) ?: [];
                    $expectedResponse = $response;

                    return $expectedResponse;
                });
        }

        $response = $client->executeRawRequest($method, $url, $data);

        $this->assertEquals($expectedResponse, $response);
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
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testExecuteRawRequestWithRetries(int $retries, int $delay)
    {
        $guzzle = new GuzzleClient(['base_uri' => 'http://httpbin.org']);

        $client = Mockery::mock(DruidClient::class, [
            ['retries' => $retries, 'retry_delay_ms' => $delay],
            $guzzle,
        ]);
        $client->makePartial();

        $url  = 'http://test.dev/v2/task';
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