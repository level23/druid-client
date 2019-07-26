<?php

namespace Level23\Druid;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Collection;
use Level23\Druid\Aggregations\AggregatorInterface;
use Level23\Druid\Exceptions\DruidCommunicationException;
use Level23\Druid\Exceptions\DruidException;
use Level23\Druid\Exceptions\DruidQueryException;
use Level23\Druid\Filters\FilterInterface;
use Psr\Log\LoggerInterface;

abstract class DruidService
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var callable
     */
    protected $logHandler;

    /**
     * @var array
     */
    protected $guzzleDefaultOptions;

    /**
     * The number of times we will try to do a retry in case of a failure.
     *
     * @var int
     */
    public $retries = 2;

    /**
     * Method which should return our broker endpoint
     *
     * @return string
     */
    abstract protected function getBrokerEndpoint(): string;

    /**
     * DruidService constructor.
     *
     * @param \GuzzleHttp\Client|null $client
     * @param array                   $guzzleDefaultOptions
     * @param callable|null           $logHandler
     */
    public function __construct(
        GuzzleClient $client = null,
        array $guzzleDefaultOptions = [],
        callable $logHandler = null
    ) {
        $this->client               = ($client ?? new GuzzleClient());
        $this->logHandler           = $logHandler;
        $this->guzzleDefaultOptions = $guzzleDefaultOptions;
    }

    protected function log($logMessage)
    {
        if ($this->logHandler) {
            call_user_func($this->logHandler, $logMessage);
        }
    }

    /**
     * Execute a druid query and return the response.
     *
     * @param array                         $query
     *
     * @param \Psr\Log\LoggerInterface|null $log
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\DruidQueryException
     * @throws \Level23\Druid\Exceptions\DruidException
     */
    protected function executeDruidQuery(array $query, LoggerInterface $log = null)
    {
        # Retry
        try {
            $this->log(
                "Executing druid query:" . json_encode($query, JSON_PRETTY_PRINT)
            );

            $response = retry($this->retries, function () use ($query) {

                $url = $this->getBrokerEndpoint();

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

                $options = array_merge($options, $this->guzzleDefaultOptions);

                $options['body'] = json_encode($query);

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

                return $response;
            }, 500);
        } catch (Exception $exception) {
            if( $exception instanceof DruidException ) {
                throw $exception;
            } else {
                throw new DruidQueryException($query, $e->getMessage(), 0, $e);
            }
        }

        $result = json_decode($response->getBody()->getContents(), true) ?: [];

        $this->log(
            'Received druid result: ' . json_encode($result, JSON_PRETTY_PRINT)
        );

        return $result;
    }

    /**
     * @param string                                 $datasource
     * @param \Illuminate\Support\Collection         $dimensions
     * @param \Illuminate\Support\Collection         $aggregations
     * @param \Carbon\Carbon                         $start   timestamp
     * @param \Carbon\Carbon                         $stop    timestamp
     * @param string                                 $granularity
     * @param \Level23\Druid\Filters\FilterInterface $filter
     * @param int                                    $limit   If higher then 0, we will limit the result.
     * @param array                                  $orderBy If the result is limited, you can order the result. Given an array
     *                                                        like:
     *                                                        [ dimension => asc, dimension2 => desc ]
     *
     * @param \Psr\Log\LoggerInterface|null          $log
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\DruidException
     * @throws \Level23\Druid\Exceptions\DruidQueryException
     */
    public function executeGroupByQuery(
        string $datasource,
        Collection $dimensions,
        Collection $aggregations,
        Carbon $start,
        Carbon $stop,
        $granularity = 'all',
        FilterInterface $filter = null,
        $limit = 0,
        $orderBy = [],
        LoggerInterface $log = null
    ) {
        $virtualColumns = [];

        $query = [
            'queryType'      => 'groupBy',
            'dataSource'     => $datasource,
            'intervals'      => [$start->toIso8601String() . '/' . $stop->toIso8601String()],
            'dimensions'     => $this->getDimensionsForQuery($dimensions, $virtualColumns),
            'filter'         => $filter->getFilter(),
            'virtualColumns' => $virtualColumns,
            'aggregations'   => $aggregations->map(function(
                AggregatorInterface $aggregator) { return $aggregator->getAggregator();
            })->toArray(),
            'granularity'    => 'all',
            'context'        => [
                'numParallelCombineThreads' => 1,
            ],
        ];

        if ($limit > 0) {
            $orderBySpec = [];
            foreach ($orderBy as $field => $direction) {
                $orderBySpec[] = [
                    'dimension' => $field,
                    'direction' => strtolower($direction) == 'asc' ? 'ascending' : 'descending',
                ];
            }

            $query['limitSpec'] = [
                'type'    => 'default',
                'limit'   => $limit,
                'columns' => $orderBySpec,
            ];
        }

        return $this->getEventData($this->executeDruidQuery($query, 60, $log));
    }

    /**
     * Build our dimensions which we can use in our druid query based on the requested dimensions
     *
     * @param array $dimensions
     * @param array $virtualColumns
     *
     * @return array
     */
    protected function getDimensionsForQuery(array $dimensions, array &$virtualColumns = [])
    {
        $queryDimensions = [];

        foreach ($dimensions as $dimension) {
            switch ($dimension) {
                case self::DIMENSION_TIME:
                    $queryDimensions[] = '__time';
                    break;

                case self::DIMENSION_COUNTRY:
                    $queryDimensions[] = 'country_id';
                    $queryDimensions[] = [
                        'type'                    => 'lookup',
                        'dimension'               => 'country_id',
                        'outputName'              => 'country',
                        'name'                    => 'sms_country',
                        'replaceMissingValueWith' => 'Unknown',
                    ];
                    break;

                case self::DIMENSION_OPERATOR:
                    $queryDimensions[] = 'operator_id';
                    $queryDimensions[] = [
                        'type'                    => 'lookup',
                        'dimension'               => 'operator_id',
                        'outputName'              => 'operator',
                        'name'                    => 'sms_operator',
                        'replaceMissingValueWith' => 'Unknown',
                    ];
                    break;

                case self::DIMENSION_FIFTEEN_MINUTE:
                    $queryDimensions[] = [
                        'type'         => 'extraction',
                        'dimension'    => '__time',
                        'outputName'   => 'datetime',
                        'extractionFn' => [
                            'type'        => 'timeFormat',
                            'format'      => 'yyyy-MM-dd HH:00:00',
                            'granularity' => 'fifteen_minute',
                        ],
                    ];
                    break;

                case self::DIMENSION_HOUR:
                    $queryDimensions[] = [
                        'type'         => 'extraction',
                        'dimension'    => '__time',
                        'outputName'   => 'hour',
                        'extractionFn' => [
                            'type'        => 'timeFormat',
                            'format'      => 'yyyy-MM-dd HH:00:00',
                            'granularity' => 'hour',
                        ],
                    ];
                    break;

                case self::DIMENSION_DAY:
                    $queryDimensions[] = [
                        'type'         => 'extraction',
                        'dimension'    => '__time',
                        'outputName'   => 'day',
                        'extractionFn' => [
                            'type'        => 'timeFormat',
                            'format'      => 'yyyy-MM-dd',
                            'granularity' => 'day',
                        ],
                    ];
                    break;

                case self::DIMENSION_DESTINATION:
                    $queryDimensions[] = 'destination';
                    $queryDimensions[] = 'number_id';
                    break;

                case self::DIMENSION_RANGE:
                    $queryDimensions[] = 'range';
                    break;

                case self::DIMENSION_SUPPLIER:
                    $queryDimensions[] = 'supplier_id';
                    $queryDimensions[] = [
                        'type'                    => 'lookup',
                        'dimension'               => 'supplier_id',
                        'outputName'              => 'supplier',
                        'name'                    => 'sms_supplier',
                        'replaceMissingValueWith' => 'Unknown',
                    ];
                    break;
            }
        }

        return array_values(array_unique($queryDimensions, SORT_REGULAR));
    }

    /**
     * Get event data from result
     *
     * @param array $druidResults
     * @param array $includeKeys
     *
     * @return array
     */
    public function getEventData($druidResults, array $includeKeys = [])
    {
        if (!$druidResults) {
            return [];
        }

        $results = [];

        foreach ($druidResults as $result) {
            if (!isset($result['event'])) {
                continue;
            }

            $obj = [];

            if (empty($includeKeys)) {
                $obj = $result['event'];
            } else {
                foreach (array_keys($result['event']) as $key) {
                    if (in_array($key, $includeKeys)) {
                        $obj[$key] = $result['event'][$key];
                    }
                }
            }

            $results[] = $obj;
        }

        return $results;
    }
}