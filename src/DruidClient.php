<?php
declare(strict_types=1);

namespace Level23\Druid;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ServerException;
use InvalidArgumentException;
use Level23\Druid\Exceptions\QueryResponseException;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Queries\QueryInterface;
use Level23\Druid\Types\Granularity;
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
    public function executeQuery(QueryInterface $druidQuery)
    {
        $query = $druidQuery->toArray();

        $this->log('Executing druid query', ['query' => $query]);

        $result = $this->executeRawQuery($this->config('broker_url') . '/druid/v2', $query);

        $this->log('Received druid response', ['response' => $result]);

        return $result;
    }

    /**
     * Execute a raw druid query and return the response.
     *
     * @param string $url The url where to send the "query" to.
     * @param array  $postData
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function executeRawQuery(string $url, array $postData = null): array
    {
        try {
            if ($postData) {
                $response = $this->client->post($url, [
                    'json' => $postData,
                ]);
            } else {
                $response = $this->client->get($url);
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
     * Return all intervals for the given dataSource
     *
     * @param string $dataSource
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function getIntervalsFor(string $dataSource): array
    {
        $url = $this->config('coordinator_url') . '/druid/coordinator/v1/datasources/' . urlencode($dataSource) . '/intervals?simple';

        return $this->executeRawQuery($url);
    }

    /**
     * Check if the given dates are valid start/end dates for an interval.
     *
     * @param \DateTime|string|int $start          DateTime object, unix timestamp or string accepted by
     *                                             DateTime::__construct
     * @param \DateTime|string|int $stop           DateTime object, unix timestamp or string accepted by
     *                                             DateTime::__construct
     * @param string               $dataSource     The dataSource
     * @param string               $sampleInterval This parameter is filled by the first matched interval, which can be
     *                                             used as an example.
     *
     * @return bool
     * @throws \Exception
     */
    public function isValidInterval($start, $stop, string $dataSource, &$sampleInterval = "")
    {
        $interval = new Interval($start, $stop);

        $fromStr = $interval->getStart()->format('Y-m-d\TH:i:s.000\Z');
        $toStr   = $interval->getStop()->format('Y-m-d\TH:i:s.000\Z');

        $foundFrom = false;
        $foundTo   = false;

        // Get all intervals and check if our interval is among them.
        $intervals = $this->getIntervalsFor($dataSource);
        foreach ($intervals as $dateStr => $info) {

            if (!$foundFrom) {
                if (substr($dateStr, 0, strlen($fromStr)) === $fromStr) {
                    $sampleInterval = $dateStr;
                    $foundFrom      = true;
                }
            }

            if (!$foundTo) {
                if (substr($dateStr, -strlen($toStr)) === $toStr) {
                    $foundTo = true;
                }
            }

            if ($foundFrom && $foundTo) {
                return true;
            }
        }

        return false;
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
    protected function getStructureForDataSourceInterval(string $dataSource, string $interval)
    {
        $url = $this->config('coordinator_url') . '/druid/coordinator/v1/datasources/' . urlencode($dataSource) . '/intervals/' . urlencode($interval) . '?full';

        // Retrieve the specs for the given datasource and interval
        $specs = $this->executeRawQuery($url);
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

        $response = $this->executeRawQuery($url, $query);

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
     *Array
     *(
     *    [task] => index_traffic-conversions-TEST2_2019-03-18T16:26:05.186Z
     *    [status] => Array
     *        (
     *            [id] => index_traffic-conversions-TEST2_2019-03-18T16:26:05.186Z
     *            [type] => index
     *            [createdTime] => 2019-03-18T16:26:05.202Z
     *            [queueInsertionTime] => 1970-01-01T00:00:00.000Z
     *            [statusCode] => SUCCESS
     *            [status] => SUCCESS
     *            [runnerStatusCode] => WAITING
     *            [duration] => 10255
     *            [location] => Array
     *                (
     *                    [host] =>
     *                    [port] => -1
     *                    [tlsPort] => -1
     *                )
     *
     *
     *            [dataSource] => traffic-conversions-TEST2
     *            [errorMsg] =>
     *
     *        )
     * )
     * @param string $taskId
     *
     * @return array
     * @throws \Exception
     */
    public function getTaskStatus(string $taskId): array
    {
        $url = $this->config('overlord_url') . '/druid/indexer/v1/task/' . urlencode($taskId) . '/status';

        return $this->executeRawQuery($url);
    }

    /**
     * @param string               $dataSource
     * @param \DateTime|string|int $start DateTime object, unix timestamp or string accepted by DateTime::__construct
     * @param \DateTime|string|int $stop  DateTime object, unix timestamp or string accepted by DateTime::__construct
     * @param string|Granularity   $segmentGranularity
     * @param bool                 $test
     *
     * @return false|mixed|string
     * @throws \Exception
     */
    public function compactSegments($dataSource, $start, $stop, $segmentGranularity = 'day', $test = false)
    {
        // First, validate the given from and to. Make sure that these
        // match the beginning and end of an interval.
        if (!self::isValidInterval($start, $stop, $dataSource, $sampleInterval)) {
            throw new InvalidArgumentException(
                'Error, invalid dates given. Please supply a complete interval!'
            );
        }

        if (is_string($segmentGranularity) && !Granularity::isValid($segmentGranularity)) {
            throw new InvalidArgumentException(
                'Error, invalid segment granularity given: ' . $segmentGranularity
            );
        }

        $interval = new Interval($start, $stop);

        $job = [
            'type'               => 'compact',
            'dataSource'         => $dataSource,
            'interval'           => $interval->getInterval(),
            'segmentGranularity' => $segmentGranularity,
            'tuningConfig'       => [
                'type'                => 'index',
                'targetPartitionSize' => 50000000,
                'maxRowsInMemory'     => 50000,
            ],
        ];

        $url = 'http://127.0.0.1:8888/druid/indexer/v1/task';

        if ($test) {
            return \GuzzleHttp\json_encode($job, JSON_PRETTY_PRINT);
        }

        // insert the task and return the task id.
        $task = $this->executeRawQuery($url, $job);

        return $task['task'];
    }

    /**
     * Create a re-index task for druid.
     *
     * The $from and $to dates are checked if they match a valid interval. Otherwise there is a
     * risk to of data loss.
     *
     * We will return an string with the task job identifier, or an exception is thrown in case of an error.
     * Example:
     * "index_traffic-conversions-2019-03-18T16:26:05.186Z"
     *
     * @param string               $dataSource
     * @param \DateTime|string|int $start DateTime object, unix timestamp or string accepted by DateTime::__construct
     * @param \DateTime|string|int $stop  DateTime object, unix timestamp or string accepted by DateTime::__construct
     * @param string|Granularity   $segmentGranularity
     * @param string|Granularity   $queryGranularity
     * @param array                $transformSpec
     * @param bool                 $test  When set to true, we will not really execute the job. However, we will return
     *                                    the JSON which should have been executed.
     *
     * @return string
     * @throws \Exception
     */
    public function reindex(
        string $dataSource,
        $start,
        $stop,
        $segmentGranularity = 'day',
        $queryGranularity = 'fifteen_minute',
        array $transformSpec = [],
        bool $test = false
    ) {

        // First, validate the given from and to. Make sure that these
        // match the beginning and end of an interval.
        if (!$this->isValidInterval($start, $stop, $dataSource, $sampleInterval)) {
            throw new InvalidArgumentException(
                'Error, invalid dates given. Please supply a complete interval!'
            );
        }

        if (is_string($segmentGranularity) && !Granularity::isValid($segmentGranularity)) {
            throw new InvalidArgumentException(
                'Error, invalid segment granularity given: ' . $segmentGranularity
            );
        }

        if (is_string($queryGranularity) && !Granularity::isValid($queryGranularity)) {
            throw new InvalidArgumentException(
                'Error, invalid query granularity given: ' . $queryGranularity
            );
        }

        $interval = new Interval($start, $stop);

        // Get the structure of the given data source.
        $structure = $this->getStructureForDataSourceInterval($dataSource, $sampleInterval);

        if (!$structure) {
            throw new QueryResponseException(
                [], 'We failed to get a druid structure for datasource ' . $dataSource
            );
        }

        $dimensionSpec = [];
        $metricSpec    = [];

        foreach ($structure['dimensions'] as $field => $type) {
            $dimensionSpec[] = [
                'name' => $field,
                'type' => $type,
            ];
        }

        foreach ($structure['metrics'] as $field => $type) {
            $metricSpec[] = [
                'name'      => $field,
                'fieldName' => $field,
                'type'      => strtolower($type) . 'Sum',
            ];
        }

        $query = [
            'type' => 'index',
            'spec' => [
                'ioConfig'     => [
                    'type'             => 'index',
                    'firehose'         => [
                        'type'       => 'ingestSegment',
                        'dataSource' => $structure['datasource'],
                        'interval'   => $interval->getInterval(),
                    ],
                    'appendToExisting' => false,
                ],
                'tuningConfig' => [
                    'type'                      => 'index',
                    'targetPartitionSize'       => 5000000,
                    'maxRowsInMemory'           => 500000,
                    'forceExtendableShardSpecs' => true,
                ],
                'dataSchema'   => [
                    'dataSource'      => $structure['datasource'],
                    'parser'          => [
                        'type'      => 'string',
                        'parseSpec' => [
                            'format'         => 'json',
                            'timestampSpec'  => [
                                'column' => '__time',
                                'format' => 'auto',
                            ],
                            'dimensionsSpec' => [
                                'dimensions' => $dimensionSpec,
                            ],
                        ],
                    ],
                    'metricsSpec'     => $metricSpec,
                    'granularitySpec' => [
                        'type'               => 'uniform',
                        'segmentGranularity' => $segmentGranularity,
                        'queryGranularity'   => $queryGranularity,
                        'rollup'             => true,
                        'intervals'          => [
                            $interval->getInterval(),
                        ],
                    ],
                    'transformSpec'   => (sizeof($transformSpec) > 0 ? $transformSpec : null),
                ],
            ],
        ];

        $url = $this->config('overlord_url') . '/druid/indexer/v1/task';

        if ($test) {
            return \GuzzleHttp\json_encode($query, JSON_PRETTY_PRINT);
        }

        // insert the task and return the task id.
        $task = $this->executeRawQuery($url, $query);

        return $task['task'];
    }
}