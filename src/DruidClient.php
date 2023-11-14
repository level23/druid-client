<?php
declare(strict_types=1);

namespace Level23\Druid;

use JsonException;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;
use Level23\Druid\Types\Granularity;
use GuzzleHttp\Client as GuzzleClient;
use Level23\Druid\Tasks\TaskInterface;
use Level23\Druid\Queries\QueryBuilder;
use Psr\Http\Message\ResponseInterface;
use Level23\Druid\Tasks\KillTaskBuilder;
use GuzzleHttp\Exception\ServerException;
use Level23\Druid\Queries\QueryInterface;
use Level23\Druid\Tasks\IndexTaskBuilder;
use Level23\Druid\Responses\TaskResponse;
use Level23\Druid\Metadata\MetadataBuilder;
use Level23\Druid\Tasks\CompactTaskBuilder;
use Level23\Druid\InputSources\DruidInputSource;
use Level23\Druid\Exceptions\QueryResponseException;
use Level23\Druid\InputSources\InputSourceInterface;
use function json_decode;

class DruidClient
{
    protected GuzzleClient $client;

    protected ?LoggerInterface $logger = null;

    /**
     * @var array<string,string|int>
     */
    protected array $config = [

        // Domain + optional port or the druid router. If this is set, it will be used for the broker,
        // coordinator and overlord.
        'router_url'            => '',

        // Domain + optional port. Don't add the api path like "/druid/v2"
        'broker_url'            => '',

        // Domain + optional port. Don't add the api path like "/druid/coordinator/v1"
        'coordinator_url'       => '',

        // Domain + optional port. Don't add the api path like "/druid/indexer/v1"
        'overlord_url'          => '',

        // The maximum duration in seconds of a druid query. If the response takes longer, we will close the connection.
        'timeout'               => 60,

        // The maximum duration in seconds of connecting to the druid instance.
        'connect_timeout'       => 10,

        // The number of times we will try to do a retry in case of a failure. So if retries is 2, we will try to
        // execute the query in worst case 3 times.
        //
        // First time is the normal attempt to execute the query.
        // Then we do the FIRST retry.
        // Then we do the SECOND retry.
        'retries'               => 2,

        // When a query fails to be executed, this is the delay before a query is retried.
        // Default is 500 ms, which is 0.5 seconds.
        //
        // Set to 0 to disable they delay between retries.
        'retry_delay_ms'        => 500,

        // Amount of time in seconds to wait till we try and poll a task status again.
        'polling_sleep_seconds' => 2,
    ];

    /**
     * DruidService constructor.
     *
     * @param array<string,string|int> $config The configuration for this client.
     * @param \GuzzleHttp\Client|null  $client
     */
    public function __construct(array $config, ?GuzzleClient $client = null)
    {
        $this->config = array_merge($this->config, $config);

        $this->client = $client ?: $this->makeGuzzleClient();
    }

    /**
     * Create a new query using the druid query builder.
     *
     * @param string             $dataSource
     * @param string|Granularity $granularity
     *
     * @return \Level23\Druid\Queries\QueryBuilder
     */
    public function query(string $dataSource = '', string|Granularity $granularity = Granularity::ALL): QueryBuilder
    {
        return new QueryBuilder($this, $dataSource, $granularity);
    }

    /**
     * Cancel the execution of a query with the given query identifier.
     *
     * @param string $identifier
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function cancelQuery(string $identifier): void
    {
        $this->executeRawRequest(
            'DELETE',
            $this->config('broker_url') . '/druid/v2/' . $identifier
        );
    }

    /**
     * Execute a druid query and return the response.
     *
     * @param \Level23\Druid\Queries\QueryInterface $druidQuery
     *
     * @return array<string|int,array<mixed>|string|int>
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function executeQuery(QueryInterface $druidQuery): array
    {
        $query = $druidQuery->toArray();

        $this->log('Executing druid query: ' . var_export($query, true));

        $result = $this->executeRawRequest('post', $this->config('broker_url') . '/druid/v2', $query);

        $this->log('Received druid response: ' . var_export($result, true));

        return $result;
    }

    /**
     * Execute a druid task and return the response.
     *
     * @param \Level23\Druid\Tasks\TaskInterface $task
     *
     * @return string The task identifier
     * @throws \Level23\Druid\Exceptions\QueryResponseException|\GuzzleHttp\Exception\GuzzleException
     */
    public function executeTask(TaskInterface $task): string
    {
        /** @var array<string,array<mixed>|int|string> $payload */
        $payload = $task->toArray();

        $this->log('Executing druid task: ' . var_export($payload, true));

        /** @var string[] $result */
        $result = $this->executeRawRequest(
            'post',
            $this->config('overlord_url') . '/druid/indexer/v1/task',
            $payload
        );

        $this->log('Received task response: ' . var_export($result, true));

        return $result['task'];
    }

    /**
     * Execute a raw druid request and return the response.
     *
     * @param string                                $method POST or GET
     * @param string                                $url    The url where to send the "query" to.
     * @param array<string,string|int|array<mixed>> $data   The data to POST or GET.
     *
     * @return array<string,array<mixed>|string|int>
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
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
            } elseif (strtolower($method) == 'delete') {
                $response = $this->client->delete($url);
            } else {
                $response = $this->client->get($url, [
                    'query' => $data,
                ]);
            }

            if ($response->getStatusCode() == 204 || $response->getStatusCode() == 202) {
                return [];
            }

            return $this->parseResponse($response, $data);
        } catch (ServerException $exception) {

            $configRetries = intval($this->config('retries', 2));
            $configDelay   = intval($this->config('retry_delay_ms', 500));
            // Should we attempt a retry?
            if ($retries++ < $configRetries) {
                $this->log('Query failed due to a server exception. Doing a retry. Retry attempt ' . $retries . ' of ' . $configRetries);
                $this->log($exception->getMessage());
                $this->log($exception->getTraceAsString());

                if ($configDelay > 0) {
                    $this->log('Sleep for ' . $configDelay . ' ms');
                    $this->usleep(($configDelay * 1000));
                }
                goto begin;
            }

            /** @var ResponseInterface $response */
            $response = $exception->getResponse();

            // Bad gateway, this happens for instance when all brokers are unavailable.
            if ($response->getStatusCode() == 502) {
                throw new QueryResponseException(
                    $data,
                    'We failed to execute druid query due to a 502 Bad Gateway response. Please try again later.',
                    $exception
                );
            }

            /** @var array<string,string> $error */
            $error = $this->parseResponse($response, $data);

            // When it's not a formatted error response from druid we rethrow the original exception
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
     * @param \Psr\Log\LoggerInterface|null $logger
     *
     * @return DruidClient
     */
    public function setLogger(?LoggerInterface $logger = null): DruidClient
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Return the logger if one is set.
     *
     * @return \Psr\Log\LoggerInterface|null
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
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
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function config(string $key, mixed $default = null): mixed
    {
        // when the broker, coordinator or overlord url is empty, then use the router url.
        $routerFallback = in_array($key, ['broker_url', 'coordinator_url', 'overlord_url']);

        if ($routerFallback) {
            return $this->config[$key] ?: $this->config('router_url', $default);
        }

        return $this->config[$key] ?? $default;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    protected function makeGuzzleClient(): GuzzleClient
    {
        return new GuzzleClient([
            'timeout'         => $this->config('timeout', 60),
            'connect_timeout' => $this->config('connect_timeout', 10),
            'headers'         => [
                'User-Agent' => 'level23 druid client package',
            ],
        ]);
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface   $response
     * @param array<string,string|int|array<mixed>> $query
     *
     * @return array<string,array<mixed>|string|int>
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    protected function parseResponse(ResponseInterface $response, array $query = []): array
    {
        $contents = $response->getBody()->getContents();
        try {
            $row = json_decode($contents, true, 512, JSON_THROW_ON_ERROR) ?: [];

            if (!is_array($row)) {
                throw new InvalidArgumentException('We failed to parse response!');
            }
        } catch (InvalidArgumentException|JsonException $exception) {
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

        return $row;
    }

    /**
     * Log a message
     *
     * @param string                                $message
     * @param array<string,array<mixed>|string|int> $context
     */
    protected function log(string $message, array $context = []): void
    {
        $this->logger?->debug($message, $context);
    }

    /**
     * @return \Level23\Druid\Metadata\MetadataBuilder
     */
    public function metadata(): MetadataBuilder
    {
        return new MetadataBuilder($this);
    }

    /**
     * Fetch the status of a druid task.
     *
     * @param string $taskId
     *
     * @return \Level23\Druid\Responses\TaskResponse
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function taskStatus(string $taskId): TaskResponse
    {
        $url = $this->config('overlord_url') . '/druid/indexer/v1/task/' . urlencode($taskId) . '/status';

        $response = $this->executeRawRequest('get', $url);

        return new TaskResponse($response);
    }

    /**
     * Waits till a druid task completes and returns the status of it.
     *
     * @param string $taskId
     *
     * @return \Level23\Druid\Responses\TaskResponse
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function pollTaskStatus(string $taskId): TaskResponse
    {
        while (true) {
            $status = $this->taskStatus($taskId);

            if ($status->getStatus() != 'RUNNING') {
                break;
            }
            sleep(intval($this->config('polling_sleep_seconds')));
        }

        return $status;
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
     * Create a kill task.
     *
     * @param string $dataSource
     *
     * @return \Level23\Druid\Tasks\KillTaskBuilder
     */
    public function kill(string $dataSource): KillTaskBuilder
    {
        return new KillTaskBuilder($this, $dataSource);
    }

    /**
     * Create an index task
     *
     * @param string                                           $dataSource
     * @param \Level23\Druid\InputSources\InputSourceInterface $inputSource
     *
     * @return \Level23\Druid\Tasks\IndexTaskBuilder
     */
    public function index(string $dataSource, InputSourceInterface $inputSource): IndexTaskBuilder
    {
        return new IndexTaskBuilder($this, $dataSource, $inputSource);
    }

    /**
     * Create a re-index task for druid.
     *
     * The $start and $stop dates are checked if they match a valid interval. Otherwise, there is a
     * risk to of data loss.
     *
     * We will return a string with the task job identifier, or an exception is thrown in case of an error.
     * Example:
     * "index_traffic-conversions-2019-03-18T16:26:05.186Z"
     *
     * @param string $dataSource
     *
     * @return \Level23\Druid\Tasks\IndexTaskBuilder
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function reindex(string $dataSource): IndexTaskBuilder
    {
        $structure = $this->metadata()->structure($dataSource);

        $builder = new IndexTaskBuilder(
            $this,
            $dataSource,
            new DruidInputSource($dataSource)
        );

        $builder->timestamp('__time', 'auto');
        foreach ($structure->dimensions as $dimension => $type) {
            $builder->dimension($dimension, $type);
        }

        foreach ($structure->metrics as $metric => $type) {
            $builder->sum($metric, $metric, $type);
        }

        return $builder;
    }
}