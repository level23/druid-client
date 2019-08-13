<?php
declare(strict_types=1);

namespace Level23\Druid;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use Level23\Druid\Exceptions\DruidCommunicationException;
use Level23\Druid\Exceptions\DruidException;
use Level23\Druid\Exceptions\DruidQueryException;
use Level23\Druid\Queries\QueryInterface;

class DruidClient
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var callable|null
     */
    protected $logHandler;

    /**
     * @var array
     */
    protected $config = [
        /**
         * domain + optional port. Don't add the api path like "/druid/v2"
         */
        'broker_url'      => '',

        /**
         * domain + optional port. Don't add the api path like "/druid/coordinator/v1"
         */
        'coordinator_url' => '',

        /**
         * domain + optional port. Don't add the api path like "/druid/indexer/v1"
         */
        'overlord_url'    => '',

        /**
         * The number of times we will try to do a retry in case of a failure.
         */
        'retries'         => 2,

        /**
         * Optional give alternative options for our guzzle connection to druid, like timeouts, headers, authorisation, etc.
         */
        'guzzle_options'  => [],

    ];

    /**
     * DruidService constructor.
     *
     * @param array         $config The configuration for this client.
     * @param callable|null $logHandler
     */
    public function __construct(array $config, callable $logHandler = null)
    {
        $this->client     = new GuzzleClient();
        $this->logHandler = $logHandler;
        $this->config     = array_merge($this->config, $config);
    }

    /**
     * Set a custom guzzle client which should be used.
     *
     * @param \GuzzleHttp\Client $client
     */
    public function setGuzzleClient(GuzzleClient $client)
    {
        $this->client = $client;
    }

    /**
     * Return the config options.
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Log a message
     *
     * @param string $logMessage
     */
    protected function log(string $logMessage)
    {
        if ($this->logHandler) {
            call_user_func($this->logHandler, $logMessage);
        }
    }

    /**
     * Execute a druid query and return the response.
     *
     * @param \Level23\Druid\Queries\QueryInterface $query
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\DruidException
     * @throws \Level23\Druid\Exceptions\DruidQueryException
     */
    public function executeDruidQuery(QueryInterface $query)
    {
        # Retry
        try {
            $jsonQuery = json_encode($query->getQuery(), JSON_PRETTY_PRINT);

            $this->log(
                "Executing druid query:" . $jsonQuery
            );

            $url = $this->config['broker_url'] . '/druid/v2';

            // these are our defaults.
            $options = [
                'timeout'         => 60,
                'allow_redirects' => true,
                'connect_timeout' => 10,

                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent'   => 'level23 druid client package',
                ],
            ];

            $options = array_merge($options, $this->config['guzzle_options']);

            $options['body'] = $jsonQuery;

            try {
                $response = $this->client->post($url, $options);
            } catch (BadResponseException $badResponseException) {
                throw new DruidCommunicationException(
                    $badResponseException->getMessage(),
                    0,
                    $badResponseException
                );
            } catch (RequestException $requestException) {
                throw new DruidCommunicationException(
                    $requestException->getMessage(),
                    0,
                    $requestException
                );
            }

            if ($response->getStatusCode() != 200) {
                throw new DruidQueryException(
                    $query,
                    'Error, failed to do a druid query due to incorrect HTTP code ' .
                    $response->getStatusCode() . '. Response: ' . $response->getBody()->getContents()
                );
            }
        } catch (Exception $exception) {
            if ($exception instanceof DruidException) {
                throw $exception;
            } else {
                throw new DruidQueryException($query, $exception->getMessage(), 0, $exception);
            }
        }

        $result = json_decode($response->getBody()->getContents(), true) ?: [];

        $this->log(
            'Received druid result: ' . json_encode($result, JSON_PRETTY_PRINT)
        );

        return $this->getEventData($result);
    }

    /**
     * Get event data from result
     *
     * @param array $druidResults
     *
     * @return array
     */
    protected function getEventData($druidResults)
    {
        if (!$druidResults) {
            return [];
        }

        $results = [];

        foreach ($druidResults as $result) {
            if (!isset($result['event'])) {
                continue;
            }

            $obj       = $result['event'];
            $results[] = $obj;
        }

        return $results;
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
}