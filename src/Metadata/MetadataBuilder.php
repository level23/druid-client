<?php
declare(strict_types=1);

namespace Level23\Druid\Metadata;

use Closure;
use DateTime;
use Exception;
use DateTimeInterface;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Types\TimeBound;
use Level23\Druid\Filters\FilterBuilder;
use Level23\Druid\Context\ContextInterface;
use Level23\Druid\DataSources\DataSourceInterface;
use Level23\Druid\Exceptions\QueryResponseException;

class MetadataBuilder
{
    protected DruidClient $client;

    public function __construct(DruidClient $client)
    {
        $this->client = $client;
    }

    /**
     * Return all intervals for the given dataSource.
     * Return an array containing the interval.
     *
     * We will store the result in static cache to prevent multiple requests.
     *
     * Example response:
     * [
     *   "2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z" => [ "size" => 75208,  "count" => 4 ],
     *   "2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z" => [ "size" => 161870, "count" => 8 ],
     * ]
     *
     * @param string $dataSource
     *
     * @return array<string,array<string,string|int>>
     *
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function intervals(string $dataSource): array
    {
        static $intervals = [];

        if (!array_key_exists($dataSource, $intervals)) {
            $url = $this->client->config('coordinator_url') . '/druid/coordinator/v1/datasources/' . urlencode($dataSource) . '/intervals';

            $intervals[$dataSource] = $this->client->executeRawRequest('get', $url, ['simple' => '']);
        }

        return $intervals[$dataSource];
    }

    /**
     * Return the time boundary for the given dataSource.
     * This finds the first and/or last occurrence of a record in the given dataSource.
     * Optionally, you can also apply a filter. For example, to only see when the first and/or last occurrence
     * was for a record where a specific condition was met.
     *
     * The return type varies per given $bound. If TimeBound::BOTH was given (or null, which is the same),
     * we will return an array with the minTime and maxTime:
     * ```
     * array(
     *  'minTime' => \DateTime object,
     *  'maxTime' => \DateTime object
     * )
     * ```
     *
     * If only one time was requested with either TimeBound::MIN_TIME or TimeBound::MAX_TIME, we will return
     * a DateTime object.
     *
     * @param string|\Level23\Druid\DataSources\DataSourceInterface $dataSource
     * @param string|\Level23\Druid\Types\TimeBound|null            $bound
     * @param \Closure|null                                         $filterBuilder
     * @param \Level23\Druid\Context\ContextInterface|null          $context
     *
     * @return ( $bound is null ? array<\DateTime> : ( $bound is "both" ? array<DateTime> : \DateTime))
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception
     */
    public function timeBoundary(
        string|DataSourceInterface $dataSource,
        null|string|TimeBound $bound = TimeBound::BOTH,
        ?Closure $filterBuilder = null,
        ?ContextInterface $context = null
    ): DateTime|array {

        $query = [
            'queryType'  => 'timeBoundary',
            'dataSource' => is_string($dataSource) ? $dataSource : $dataSource->toArray(),
        ];

        if (is_string($bound)) {
            $bound = TimeBound::from($bound);
        }

        if (!empty($bound) && $bound != TimeBound::BOTH) {
            $query['bound'] = $bound->value;
        }

        if ($filterBuilder) {
            $builder = new FilterBuilder();
            call_user_func($filterBuilder, $builder);
            $filter = $builder->getFilter();

            if ($filter) {
                $query['filter'] = $filter->toArray();
            }
        }

        if ($context) {
            $query['context'] = $context->toArray();
        }

        $url = $this->client->config('broker_url') . '/druid/v2';

        /** @var array<int,null|array<string,string[]|string>> $response */
        $response = $this->client->executeRawRequest('post', $url, $query);

        if (!empty($response[0])
            && !empty($response[0]['result'])
            && is_array($response[0]['result'])
        ) {
            if (sizeof($response[0]['result']) == 1) {
                $dateString = reset($response[0]['result']);
                $date       = DateTime::createFromFormat('Y-m-d\TH:i:s.000\Z', $dateString);

                if (!$date) {
                    throw new Exception('Failed to parse time: ' . $dateString);
                }

                return $date;
            } else {
                $result = [];
                foreach ($response[0]['result'] as $key => $dateString) {
                    /** @var string $key */
                    $date = DateTime::createFromFormat('Y-m-d\TH:i:s.000\Z', $dateString);

                    if (!$date) {
                        throw new Exception('Failed to parse time: ' . $dateString);
                    }

                    $result[$key] = $date;
                }

                return $result;
            }
        }

        throw new Exception('Received incorrect response: ' . var_export($response, true));
    }

    /**
     * Returns a map of segment intervals contained within the specified interval to a map of segment metadata to a set
     * of server names that contain the segment for an interval.
     * The latest intervals will come as first, the oldest as last.
     *
     * Example response:
     *
     * Array
     * (
     *     [2017-01-01T00:00:00.000Z/2017-01-02T00:00:00.000Z] => Array
     *         (
     *             [traffic-conversions_2017-01-01T00:00:00.000Z_2017-01-02T00:00:00.000Z_2019-05-15T11:29:56.874Z] =>
     *             Array
     *                 (
     *                     [metadata] => Array
     *                         (
     *                             [dataSource] => traffic-conversions
     *                             [interval] => 2017-01-01T00:00:00.000Z/2017-01-02T00:00:00.000Z
     *                             [version] => 2019-05-15T11:29:56.874Z
     *                             [loadSpec] => Array
     *                                 (
     *                                     [type] => s3_zip
     *                                     [bucket] => level23-druid-data
     *                                     [key] =>
     *                                     druid/segments/traffic-conversions/2017-01-01T00:00:00.000Z_2017-01-02T00:00:00.000Z/2019-05-15T11:29:56.874Z/0/index.zip
     *                                     [S3Schema] => s3n
     *                                 )
     *
     *                             [dimensions] =>
     *                             country_iso,flags,mccmnc,offer_id,product_type_id,promo_id,test_data_id,test_data_reason,third_party_id
     *                             [metrics] => conversion_time,conversions,revenue_external,revenue_internal
     *                             [shardSpec] => Array
     *                                 (
     *                                     [type] => numbered
     *                                     [partitionNum] => 0
     *                                     [partitions] => 0
     *                                 )
     *
     *                             [binaryVersion] => 9
     *                             [size] => 272709
     *                             [identifier] =>
     *                             traffic-conversions_2017-01-01T00:00:00.000Z_2017-01-02T00:00:00.000Z_2019-05-15T11:29:56.874Z
     *                         )
     *
     *                     [servers] => Array
     *                         (
     *                             [0] => 172.31.23.160:8083
     *                             [1] => 172.31.3.204:8083
     *                         )
     *
     *                 )
     *
     *         )
     *
     * )
     *
     * @param string $dataSource
     * @param string $interval
     *
     * @return array<string,array<mixed>|string|int>
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function interval(string $dataSource, string $interval): array
    {
        $url = $this->client->config('coordinator_url') .
            '/druid/coordinator/v1/datasources/' . urlencode($dataSource) .
            '/intervals/' . urlencode($interval);

        return $this->client->executeRawRequest('get', $url, ['full' => '']);
    }

    /**
     * Get the columns for the given interval. This will return something like this:
     *
     *   Array
     *  (
     *      0 => Array
     *          (
     *              [field] => __time
     *              [type] => LONG
     *              [hasMultipleValues] =>
     *              [size] => 0
     *              [cardinality] =>
     *              [minValue] =>
     *              [maxValue] =>
     *              [errorMessage] =>
     *          )
     *      1 => Array
     *          (
     *              [field] => delta
     *              [type] => LONG
     *              [hasMultipleValues] =>
     *              [size] => 0
     *              [cardinality] =>
     *              [minValue] =>
     *              [maxValue] =>
     *              [errorMessage] =>
     *          )
     *      2 => Array
     *          (
     *              [field] => cityName
     *              [type] => STRING
     *              [hasMultipleValues] =>
     *              [size] => 0
     *              [cardinality] => 59
     *              [minValue] => af
     *              [maxValue] => zm
     *              [errorMessage] =>
     *          )
     *      3 => Array
     *          (
     *              [field] => comment
     *              [type] => STRING
     *              [hasMultipleValues] =>
     *              [size] => 0
     *              [cardinality] => 84
     *              [minValue] =>
     *              [maxValue] => 74807
     *              [errorMessage] =>
     *          )
     *      4 => Array
     *          (
     *              [field] => added
     *              [type] => LONG
     *              [hasMultipleValues] =>
     *              [size] => 0
     *              [cardinality] =>
     *              [minValue] =>
     *              [maxValue] =>
     *              [errorMessage] =>
     *          )
     *  )
     *
     * @param string                             $dataSource
     * @param \DateTimeInterface|int|string      $start
     * @param \DateTimeInterface|int|string|null $stop
     *
     * @return array<int,array<string,string>>
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception
     */
    protected function getColumnsForInterval(
        string $dataSource,
        DateTimeInterface|int|string $start,
        DateTimeInterface|int|string|null $stop = null
    ): array {
        $response = $this->client->query($dataSource)
            ->interval($start, $stop)
            ->segmentMetadata();

        $columns = [];

        $rows = $response->data();

        if (isset($rows[0])) {

            /** @var array<string,array<string,array<string,string>>> $row */
            $row = $rows[0];

            if (isset($row['columns'])) {
                array_walk($row['columns'], function ($value, $key) use (&$columns) {
                    $columns[] = array_merge($value, ['field' => $key]);
                });
            }
        }

        return $columns;
    }

    /**
     * Return the total number of rows for the given interval
     *
     * @param string                             $dataSource The name of the dataSource where you want to count the
     *                                                       rows for
     * @param \DateTimeInterface|int|string      $start      The start of the interval.
     * @param \DateTimeInterface|int|string|null $stop       The end of the interval, or null when it was given as a
     *                                                       "date/date" interval in the $start parameter.
     *
     * @return int
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception
     */
    public function rowCount(
        string $dataSource,
        DateTimeInterface|int|string $start,
        DateTimeInterface|int|string|null $stop = null
    ): int {
        $response = $this->client->query($dataSource)
            ->interval($start, $stop)
            ->segmentMetadata();

        $totalRows = 0;
        foreach ($response->data() as $row) {
            if (isset($row['numRows'])) {
                $totalRows += intval($row['numRows']);
            }
        }

        return $totalRows;
    }

    /**
     * Return the druid interval by the shorthand "first" or "last".
     *
     * We will return something like "2017-01-01T00:00:00.000Z/2017-01-02T00:00:00.000Z"
     *
     * We will return an empty array when no interval data is found.
     *
     * @param string $dataSource
     * @param string $shortHand
     *
     * @return string
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getIntervalByShorthand(string $dataSource, string $shortHand): string
    {
        // Get the interval which we will use to do a "structure" scan.
        $shortHand = strtolower($shortHand);
        if ($shortHand != 'last' && $shortHand != 'first') {
            throw new InvalidArgumentException('Only shorthand "first" and "last" are supported!');
        }

        $rawIntervals = $this->intervals($dataSource);

        $intervals = array_keys($rawIntervals);

        $result = ($shortHand == 'last') ? ($intervals[0] ?? '') : ($intervals[count($intervals) - 1] ?? '');

        if (empty($result)) {
            $this->client->getLogger()?->warning(
                'Failed to get ' . $shortHand . ' interval! ' .
                'We got ' . count($rawIntervals) . ' intervals: ' . var_export($intervals, true)
            );
        }

        return $result;
    }

    /**
     * Generate a structure class for the given dataSource.
     *
     * @param string $dataSource
     * @param string $interval "last", "first" or a raw interval string as returned by druid.
     *
     * @return \Level23\Druid\Metadata\Structure
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function structure(string $dataSource, string $interval = 'last'): Structure
    {
        // shorthand given? Then retrieve the real interval for them.
        if (in_array(strtolower($interval), ['first', 'last'])) {
            $interval = $this->getIntervalByShorthand($dataSource, $interval);
        }

        if (empty($interval)) {
            throw new InvalidArgumentException(
                'Error, interval "' . $interval . '" is invalid. Maybe there are no intervals for this dataSource?'
            );
        }

        $rawStructure = $this->interval($dataSource, $interval);

        $structureData = reset($rawStructure);
        if (!$structureData || !is_array($structureData)) {
            throw new QueryResponseException([],
                'We failed to retrieve a correct structure for dataSource: ' . $dataSource . '.' . PHP_EOL .
                'Failed to parse raw interval structure data: ' . var_export($rawStructure, true)

            );
        }

        /** @var array<string|string[]> $data */
        $data = reset($structureData);

        $dimensionFields = explode(',', $data['metadata']['dimensions'] ?? '');
        $metricFields    = explode(',', $data['metadata']['metrics'] ?? '');

        $dimensions = [];
        $metrics    = [];

        $columns = $this->getColumnsForInterval($dataSource, $interval);

        foreach ($columns as $info) {
            $column = $info['field'];

            if (in_array($column, $dimensionFields)) {
                $dimensions[$column] = $info['type'];
            }
            if (in_array($column, $metricFields)) {
                $metrics[$column] = $info['type'];
            }
        }

        return new Structure($dataSource, $dimensions, $metrics);
    }

    /**
     * Return a list of all known dataSources
     *
     * @return array<string>
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function dataSources(): array
    {
        $url = $this->client->config('coordinator_url') . '/druid/coordinator/v1/datasources';

        /** @var array<int,string> $dataSources */
        $dataSources = $this->client->executeRawRequest('get', $url);

        return $dataSources;
    }
}