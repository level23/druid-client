<?php
declare(strict_types=1);

namespace Level23\Druid;

use Psr\Log\LoggerInterface;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use Level23\Druid\Queries\QueryInterface;
use GuzzleHttp\Exception\ServerException;
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

        $result = $this->executeRawQuery($query);

        $this->log('Received druid response', ['response' => $result]);

        return $result;
    }

    /**
     * Execute a raw druid query and return the response.
     *
     * @param array $query
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function executeRawQuery(array $query): array
    {
        try {
            $response = $this->client->post('druid/v2', [
                'json' => $query,
            ]);

            return $this->parseResponse($response);
        } catch (ServerException $exception) {

            $response = $exception->getResponse();

            if(!$response instanceof ResponseInterface) {
                throw $exception;
            }

            $error = $this->parseResponse($response);

            // When its not a formatted error response from druid we rethrow the original exception
            if (!isset($error['error'], $error['errorMessage'])) {
                throw $exception;
            }

            throw new QueryResponseException(
                $query,
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
            'base_uri'        => $this->config('broker_url'),
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
}