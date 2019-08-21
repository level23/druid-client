<?php
declare(strict_types=1);

namespace Level23\Druid;

use Psr\Log\LoggerInterface;
use GuzzleHttp\Client as GuzzleClient;
use Level23\Druid\Tasks\TaskInterface;
use Level23\Druid\Queries\QueryBuilder;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ServerException;
use Level23\Druid\Queries\QueryInterface;
use Level23\Druid\Tasks\IndexTaskBuilder;
use Level23\Druid\Metadata\MetadataBuilder;
use Level23\Druid\Tasks\CompactTaskBuilder;
use Level23\Druid\Firehoses\IngestSegmentFirehose;
use Level23\Druid\Exceptions\QueryResponseException;

class DruidClient
{
    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * @var LoggerInterface|null
     */
    protected $logger = null;

    /**
     * @var array
     */
    protected $config = [
        'broker_url'      => '', // domain + optional port. Don't add the api path like "/druid/v2"
        'coordinator_url' => '', // domain + optional port. Don't add the api path like "/druid/coordinator/v1"
        'overlord_url'    => '', // domain + optional port. Don't add the api path like "/druid/indexer/v1"
        'retries'         => 2, // The number of times we will try to do a retry in case of a failure.
    ];

    /**
     * DruidService constructor.
     *
     * @param array                   $config The configuration for this client.
     * @param \GuzzleHttp\Client|null $client
     */
    public function __construct(array $config, GuzzleClient $client = null)
    {
        $this->config = array_merge($this->config, $config);

        $this->client = $client ?: $this->makeGuzzleClient();
    }

    /**
     * Create a new query using the druid query builder.
     *
     * @param string                                  $dataSource
     * @param string|\Level23\Druid\Types\Granularity $granularity
     *
     * @return \Level23\Druid\Queries\QueryBuilder
     */
    public function query(string $dataSource, $granularity = 'all'): QueryBuilder
    {
        return new QueryBuilder($this, $dataSource, $granularity);
    }

    /**
     * Execute a druid query and return the response.
     *
     * @param \Level23\Druid\Queries\QueryInterface $druidQuery
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function executeQuery(QueryInterface $druidQuery): array
    {
        $query = $druidQuery->toArray();

        $this->log('Executing druid query', ['query' => $query]);

        $result = $this->executeRawRequest('post', $this->config('broker_url') . '/druid/v2', $query);

        $this->log('Received druid response', ['response' => $result]);

        return $result;
    }

    /**
     * Execute a druid task and return the response.
     *
     * @param \Level23\Druid\Tasks\TaskInterface $task
     *
     * @return string The task identifier
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function executeTask(TaskInterface $task): string
    {
        $payload = $task->toArray();

        $this->log('Executing druid task', ['task' => $payload]);

        $result = $this->executeRawRequest(
            'post',
            $this->config('overlord_url') . '/druid/indexer/v1/task',
            $payload
        );

        $this->log('Received task response', ['response' => $result]);

        return $result['task'];
    }

    /**
     * Execute a raw druid request and return the response.
     *
     * @param string $method POST or GET
     * @param string $url    The url where to send the "query" to.
     * @param array  $data   The data to POST or GET.
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function executeRawRequest(string $method, string $url, array $data = null): array
    {
        try {
            if (strtolower($method) == 'post') {
                $response = $this->client->post($url, [
                    'json' => $data,
                ]);
            } else {
                $response = $this->client->get($url, [
                    'query' => $data,
                ]);
            }

            if ($response->getStatusCode() == 204) {
                return [];
            }

            return $this->parseResponse($response);
        } catch (ServerException $exception) {

            $response = $exception->getResponse();

            if (!$response instanceof ResponseInterface) {
                throw $exception;
            }

            $error = $this->parseResponse($response);

            // When its not a formatted error response from druid we rethrow the original exception
            if (!isset($error['error'], $error['errorMessage'])) {
                throw $exception;
            }

            throw new QueryResponseException(
                $data ?: [],
                sprintf('%s: %s', $error['error'], $error['errorMessage']),
                $exception
            );
        }
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return DruidClient
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Set a custom guzzle client which should be used.
     *
     * @param GuzzleClient $client
     */
    public function setGuzzleClient(GuzzleClient $client)
    {
        $this->client = $client;
    }

    /**
     * Get the value of the config key
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed|null
     */
    public function config($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    protected function makeGuzzleClient(): GuzzleClient
    {
        return new GuzzleClient([
            'timeout'         => 60,
            'connect_timeout' => 10,
            'headers'         => [
                'User-Agent' => 'level23 druid client package',
            ],
        ]);
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return array
     */
    protected function parseResponse(ResponseInterface $response)
    {
        return \GuzzleHttp\json_decode($response->getBody()->getContents(), true) ?: [];
    }

    /**
     * Log a message
     *
     * @param string $message
     * @param array  $context
     */
    protected function log(string $message, array $context = [])
    {
        if ($this->logger) {
            $this->logger->info($message, $context);
        }
    }

    /**
     * @return \Level23\Druid\Metadata\MetadataBuilder
     */
    public function metadata(): MetadataBuilder
    {
        return new MetadataBuilder($this);
    }

    /**
     * Fetch the status of a druid task. We will return an array like this:
     *
     * [
     *    [id] => index_traffic-conversions-TEST2_2019-03-18T16:26:05.186Z
     *    [type] => index
     *    [createdTime] => 2019-03-18T16:26:05.202Z
     *    [queueInsertionTime] => 1970-01-01T00:00:00.000Z
     *    [statusCode] => SUCCESS
     *    [status] => SUCCESS
     *    [runnerStatusCode] => WAITING
     *    [duration] => 10255
     *    [location] => Array
     *        (
     *            [host] =>
     *            [port] => -1
     *            [tlsPort] => -1
     *        )
     *
     *
     *    [dataSource] => traffic-conversions-TEST2
     *    [errorMsg] =>
     * ]
     *
     * @param string $taskId
     *
     * @return array
     * @throws \Exception
     */
    public function taskStatus(string $taskId): array
    {
        $url = $this->config('overlord_url') . '/druid/indexer/v1/task/' . urlencode($taskId) . '/status';

        $response = $this->executeRawRequest('get', $url);

        return $response['status'] ?: [];
    }

    /**
     * Build a new compact task.
     *
     * @param string $dataSource
     *
     * @return \Level23\Druid\Tasks\CompactTaskBuilder
     */
    public function compact(string $dataSource): CompactTaskBuilder
    {
        return new CompactTaskBuilder($this, $dataSource);
    }

    /**
     * Create a re-index task for druid.
     *
     * The $start and $stop dates are checked if they match a valid interval. Otherwise there is a
     * risk to of data loss.
     *
     * We will return an string with the task job identifier, or an exception is thrown in case of an error.
     * Example:
     * "index_traffic-conversions-2019-03-18T16:26:05.186Z"
     *
     * @param string $dataSource
     *
     * @return \Level23\Druid\Tasks\IndexTaskBuilder
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function reindex(string $dataSource): IndexTaskBuilder
    {
        $structure = $this->metadata()->structure($dataSource);

        $builder = new IndexTaskBuilder($this, $dataSource, IngestSegmentFirehose::class);

        foreach ($structure->dimensions as $dimension => $type) {
            $builder->dimension($dimension, $type);
        }

        foreach ($structure->metrics as $metric => $type) {
            $builder->sum($metric, $metric, $type);
        }

        return $builder;
    }
}