<?php
declare(strict_types=1);

namespace Level23\Druid\Metadata;

use Level23\Druid\DruidClient;
use Level23\Druid\Exceptions\QueryResponseException;

class MetadataBuilder
{
    /**
     * @var \Level23\Druid\DruidClient
     */
    protected $client;

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
            $url = $this->client->config('coordinator_url') . '/druid/coordinator/v1/datasources/' . urlencode($dataSource) . '/intervals?simple';

            $intervals[$dataSource] = $this->client->executeRawRequest('get', $url);
        }

        return $intervals[$dataSource];
    }

    /**
     * Returns a map of segment intervals contained within the specified interval to a map of segment metadata to a set
     * of server names that contain the segment for an interval.
     * The latest intervals will come as first, the olderst as last.
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
     * @return array
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function interval(string $dataSource, string $interval): array
    {
        $url = 'http://127.0.0.1:8888/druid/coordinator/v1/datasources/' . urlencode($dataSource) . '/intervals/' . urlencode($interval) . '?full';

        return $this->client->executeRawRequest('get', $url);
    }

    /**
     * Generate a structure class for the given dataSource.
     *
     * @param string $dataSource
     * @param string $interval "last", "first" or a raw interval string as returned by druid.
     *
     * @return \Level23\Druid\Metadata\Structure
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \Exception
     */
    public function structure(string $dataSource, string $interval = 'last'): Structure
    {
        // Get the interval which we will use to do a "structure" scan.
        $interval = strtolower($interval);
        if ($interval == 'last' || $interval == 'first') {
            $intervals = array_keys($this->intervals($dataSource));

            if ($interval == 'last') {
                $interval = $intervals[0];
            } else {
                $interval = $intervals[count($intervals) - 1];
            }
        }

        $rawStructure = $this->interval($dataSource, $interval);

        if (!$rawStructure) {
            throw new QueryResponseException(
                [], 'We failed to retrieve a correct structure for datasource ' . $dataSource
            );
        }

        $rawStructure = reset($rawStructure);
        if (!$rawStructure) {
            throw new QueryResponseException(
                [], 'We failed to retrieve a correct structure for datasource ' . $dataSource
            );
        }

        $data = reset($rawStructure);

        $dimensionFields = explode(',', $data['metadata']['dimensions']);
        $metricFields    = explode(',', $data['metadata']['metrics']);

        $response = $this->client->query($dataSource)
            ->interval($interval)
            ->segmentMetadata();

        if (empty($response[0]['columns'])) {
            throw new QueryResponseException(
                [], 'We failed to parse the response of our segmentMetadataQuery!'
            );
        }

        $dimensions = [];
        $metrics    = [];

        foreach ($response[0]['columns'] as $column => $info) {
            if (in_array($column, $dimensionFields)) {
                $dimensions[$column] = $info['type'];
            }
            if (in_array($column, $metricFields)) {
                $metrics[$column] = $info['type'];
            }
        }

        return new Structure($dataSource, $dimensions, $metrics);
    }
}