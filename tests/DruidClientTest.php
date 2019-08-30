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
     * @throws \ReflectionException
     */
    public function testSetGuzzleClient()
    {
        $guzzle = new GuzzleClient(['base_uri' => 'http://httpbin.org']);

        $this->client->setGuzzleClient($guzzle);

        $this->assertEquals($guzzle,
            $this->getProperty($this->client, 'client')
        );
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

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testExecuteDruidQuery()
    {
        $client = Mockery::mock(DruidClient::class, [[]]);
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
        $logger->shouldReceive('info')->twice();

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
        $client = Mockery::mock(DruidClient::class, [[]]);
        $client->makePartial();

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->times(3);

        $client->setLogger($logger);

        $this->expectException(QueryResponseException::class);
        $this->expectExceptionMessage('Failed to parse druid response. Invalid json?');

        /** @noinspection PhpUndefinedMethodInspection */
        $client->shouldAllowMockingProtectedMethods()
            ->parseResponse(new GuzzleResponse(200, [], 'something'));
    }

    public function testConfig()
    {
        $client = new DruidClient(['pieter' => 'okay']);

        $this->assertEquals('okay', $client->config('pieter'));
        $this->assertEquals('bar', $client->config('foo', 'bar'));
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

        $client = Mockery::mock(DruidClient::class, [['retries' => 0]]);
        $client->makePartial();

        $url  = 'http://test.dev/v2/task';
        $data = ['payload' => 'here'];

        $guzzle = Mockery::mock(GuzzleClient::class);
        $guzzle->shouldReceive(strtolower($method))
            ->once()
            ->with($url, (strtolower($method) == 'post' ? ['json' => $data] : ['query' => $data]))
            ->andReturnUsing($responseFunction);

        $client->setGuzzleClient($guzzle);

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
     *
     * @param int $retries
     * @param int $delay
     *
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testExecuteRawRequestWithRetries(int $retries, int $delay)
    {
        $client = Mockery::mock(DruidClient::class, [['retries' => $retries, 'retry_delay_ms' => $delay]]);
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

        $client->shouldAllowMockingProtectedMethods()
            ->shouldReceive('usleep')
            ->with(($delay * 1000))
            ->times($retries);

        $client->setGuzzleClient($guzzle);

        $this->expectException(QueryResponseException::class);
        $this->expectExceptionMessage('We failed to execute druid query due to a 502 Bad Gateway response.');

        $client->executeRawRequest('get', $url, $data);
    }
}