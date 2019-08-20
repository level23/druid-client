<?php
declare(strict_types=1);

namespace Level23\Druid;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ServerException;
use Level23\Druid\Exceptions\QueryResponseException;
use Level23\Druid\Queries\QueryInterface;
use Level23\Druid\Tasks\IngestSegmentFirehose;
use Level23\Druid\Tasks\TaskInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

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
     * @return \Level23\Druid\QueryBuilder
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

        $result = $this->executeRawRequest($this->config('broker_url') . '/druid/v2', $query);

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
            $this->config('overlord_url') . '/druid/indexer/v1/task', $payload
        );

        $this->log('Received task response', ['response' => $result]);

        return $result['task'];
    }

    /**
     * Execute a raw druid request and return the response.
     *
     * @param string $url      The url where to send the "query" to.
     * @param array  $postData The json data to POST. If not given, we will do a GET request.
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function executeRawRequest(string $url, array $postData = null): array
    {
        try {
            if ($postData) {
                $response = $this->client->post($url, [
                    'json' => $postData,
                ]);
            } else {
                $response = $this->client->get($url);
            }

            if ($response->getStatusCode() == 204) {
                throw new QueryResponseException($postData ?: [], $response->getReasonPhrase());
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
                $postData ?: [],
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
    protected function config($key, $default = null)
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
     * Return all intervals for the given dataSource.
     * Return an array containing the interval.
     *
     * We will store the result in static cache to prevent multiple requests.
     *
     * Example response:
     * [
     *   "2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z" => [ "size": 75208, "count": 4 ],
     *   "2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z" => [ "size": 161870, "count": 8 ],
     * ]
     *
     * @param string $dataSource
     *
     * @return array
     *
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function intervals(string $dataSource): array
    {
        static $intervals = [];

        if (!array_key_exists($dataSource, $intervals)) {
            $url = $this->config('coordinator_url') . '/druid/coordinator/v1/datasources/' . urlencode($dataSource) . '/intervals?simple';

            $intervals[$dataSource] = $this->executeRawRequest($url);
        }

        return $intervals[$dataSource];
    }

    /**
     * Return a list with dimensions and metrics.
     * Example response:
     * Array
     * (
     *    [datasource] => traffic-conversions
     *    [interval] => 2019-02-11T00:00:00.000Z/2019-02-12T00:00:00.000Z
     *    [metrics] => Array
     *    (
     *      [conversion_time] => LONG
     *      [conversions] => LONG
     *      [revenue_external] => DOUBLE
     *      [revenue_internal] => DOUBLE
     *    )
     *    [dimensions] => Array
     *    (
     *      [added] => LONG
     *      [country_iso] => STRING
     *      [flags] => LONG
     *      [mccmnc] => STRING
     *      [offer_id] => LONG
     *      [product_type_id] => LONG
     *      [promo_id] => LONG
     *      [promo_info] => STRING
     *      [test_data_id] => LONG
     *      [test_data_reason] => LONG
     *      [third_party_id] => LONG
     *    )
     * )
     *
     *
     * @param string $dataSource
     * @param string $interval
     *
     * @return array
     * @throws \Exception
     */
    public function getStructureForDataSourceInterval(string $dataSource, string $interval): array
    {
        $url = $this->config('coordinator_url') . '/druid/coordinator/v1/datasources/' . urlencode($dataSource) . '/intervals/' . urlencode($interval) . '?full';

        // Retrieve the specs for the given datasource and interval
        $specs = $this->executeRawRequest($url);
        if (!$specs) {
            return [];
        }

        $list = reset($specs);
        if (!$list) {
            return [];
        }

        $data = reset($list);

        $dimensions = explode(',', $data['metadata']['dimensions']);
        $metrics    = explode(',', $data['metadata']['metrics']);

        $returnData = [
            'datasource' => $dataSource,
            'interval'   => $interval,
            'metrics'    => [],
            'dimensions' => [],
        ];

        $query = [
            'queryType'  => 'segmentMetadata',
            'dataSource' => $dataSource,
            'intervals'  => [$interval],
        ];

        $response = $this->executeRawRequest($url, $query);

        if (empty($response[0]['columns'])) {
            return [];
        }

        foreach ($response[0]['columns'] as $column => $info) {
            if (in_array($column, $dimensions)) {
                $returnData['dimensions'][$column] = $info['type'];
            }
            if (in_array($column, $metrics)) {
                $returnData['metrics'][$column] = $info['type'];
            }
        }

        return $returnData;
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

        $response = $this->executeRawRequest($url);

        return $response['status'] ?: [];
    }

    /**
     * Build a new compact task.
     *
     * @param string $dataSource
     *
     * @return \Level23\Druid\CompactTaskBuilder
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
     * @param string               $dataSource
     * @param \DateTime|string|int $start DateTime object, unix timestamp or string accepted by DateTime::__construct
     * @param \DateTime|string|int $stop  DateTime object, unix timestamp or string accepted by DateTime::__construct
     *
     * @return string
     * @throws \Exception
     */
    public function reindex(string $dataSource): IndexTaskBuilder
    {
        return new IndexTaskBuilder($this, $dataSource, IngestSegmentFirehose::class);
    }
}