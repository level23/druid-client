<?php
declare(strict_types=1);

namespace Level23\Druid;

use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use GuzzleHttp\Client as GuzzleClient;
use Level23\Druid\Tasks\TaskInterface;
use Level23\Druid\Queries\SelectQuery;
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
    protected $pagingIdentifier = [];

    /**
     * @var array
     */
    protected $config = [
        /**
         * Domain + optional port. Don't add the api path like "/druid/v2"
         */
        'broker_url'      => '',

        /**
         * Domain + optional port. Don't add the api path like "/druid/coordinator/v1"
         */
        'coordinator_url' => '',

        /**
         * Domain + optional port. Don't add the api path like "/druid/indexer/v1"
         */
        'overlord_url'    => '',

        /**
         * The number of times we will try to do a retry in case of a failure. So if retries is 2, we will try to
         * execute the query in worst case 3 times.
         *
         * First time is the normal attempt to execute the query.
         * Then we do the FIRST retry.
         * Then we do the SECOND retry.
         */
        'retries'         => 2,

        /**
         * When a query fails to be executed, this is the delay before a query is retried.
         * Default is 500 ms, which is 0.5 seconds.
         *
         * Set to 0 to disable they delay between retries.
         */
        'retry_delay_ms'  => 500,
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
     * @param string $dataSource
     * @param string $granularity
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

        if ($druidQuery instanceof SelectQuery) {
            $this->pagingIdentifier = $result[0]['result']['pagingIdentifiers'];
        }

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
    public function executeRawRequest(string $method, string $url, array $data = []): array
    {
        $retries = 0;

        begin:
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

            return $this->parseResponse($response, $data);
        } catch (ServerException $exception) {

            $configRetries = $this->config('retries', 2);
            $configDelay   = $this->config('retry_delay_ms', 500);
            // Should we attempt a retry?
            if ($retries++ < $configRetries) {

                if ($configDelay > 0) {
                    $this->usleep(($configDelay * 1000));
                }
                goto begin;
            }

            $response = $exception->getResponse();

            if (!$response instanceof ResponseInterface) {
                throw $exception;
            }

            // Bad gateway, this happens for instance when all brokers are unavailable.
            if ($response->getStatusCode() == 502) {
                throw new QueryResponseException(
                    $data,
                    'We failed to execute druid query due to a 502 Bad Gateway response. Please try again later.',
                    $exception
                );
            }

            $error = $this->parseResponse($response, $data);

            // When its not a formatted error response from druid we rethrow the original exception
            if (!isset($error['error'], $error['errorMessage'])) {
                throw $exception;
            }

            throw new QueryResponseException(
                $data,
                sprintf('%s: %s', $error['error'], $error['errorMessage']),
                $exception
            );
        }
    }

    /**
     * @param int $microSeconds
     *
     * @codeCoverageIgnore
     */
    protected function usleep(int $microSeconds): void
    {
        usleep($microSeconds);
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return DruidClient
     */
    public function setLogger(LoggerInterface $logger): DruidClient
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Set a custom guzzle client which should be used.
     *
     * @param GuzzleClient $client
     *
     * @return \Level23\Druid\DruidClient
     */
    public function setGuzzleClient(GuzzleClient $client): DruidClient
    {
        $this->client = $client;

        return $this;
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
     * @param array                               $query
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    protected function parseResponse(ResponseInterface $response, array $query = []): array
    {
        $contents = $response->getBody()->getContents();
        try {
            $response = \GuzzleHttp\json_decode($contents, true) ?: [];
        } catch (InvalidArgumentException $exception) {
            $this->log('We failed to decode druid response. ');
            $this->log('Status code: ' . $response->getStatusCode());
            $this->log('Response body: ' . $contents);

            throw new QueryResponseException(
                $query,
                'Failed to parse druid response. Invalid json? Status code(' . $response->getStatusCode() . '). ' .
                'Response body: ' . $contents,
                $exception
            );
        }

        return $response;
    }

    /**
     * Log a message
     *
     * @param string $message
     * @param array  $context
     */
    protected function log(string $message, array $context = []): void
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

        return $response['status'] ?? [];
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

    /**
     * Return the last known paging identifier known by a select query. (If any is executed).
     * If no paging identifier is known, an empty array is returned.
     *
     * @return array
     */
    public function getPagingIdentifier(): array
    {
        return $this->pagingIdentifier;
    }
}