<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Metadata;

use Mockery;
use tests\TestCase;
use Level23\Druid\DruidClient;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\Metadata\MetadataBuilder;

class MetadataBuilderTest extends TestCase
{
    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testIntervals()
    {
        $intervals = [
            "2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z" => ["size" => 75208, "count" => 4],
            "2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z" => ["size" => 161870, "count" => 8],
        ];

        $client = Mockery::mock(DruidClient::class, [[]]);
        $client->shouldReceive('config')
            ->once()
            ->with('coordinator_url')
            ->andReturn('http://coordinator.url');

        $client->shouldReceive('executeRawRequest')
            ->once()
            ->with('get',
                'http://coordinator.url/druid/coordinator/v1/datasources/' . urlencode('dataSource') . '/intervals?simple')
            ->andReturn($intervals);

        $builder  = new MetadataBuilder($client);
        $response = $builder->intervals('dataSource');

        $this->assertEquals($intervals, $response);

        // A second time should not trigger the "config" and "executeRawRequest" again, as we use static cache.
        $response = $builder->intervals('dataSource');

        $this->assertEquals($intervals, $response);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testInterval()
    {
        $client = Mockery::mock(DruidClient::class, [[]]);

        $builder = new MetadataBuilder($client);

        $intervalResonse = ['druid' => ['response' => 'here']];

        $client->shouldReceive('config')
            ->once()
            ->with('coordinator_url')
            ->andReturn('http://coordinator.url');

        $client->shouldReceive('executeRawRequest')
            ->once()
            ->with('get',
                'http://coordinator.url/druid/coordinator/v1/datasources/' . urlencode('dataSource') .
                '/intervals/' . urlencode('2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z') . '?full'
            )
            ->andReturn($intervalResonse);

        $response = $builder->interval(
            'dataSource',
            '2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z'
        );

        $this->assertEquals($intervalResonse, $response);
    }

    public function structureDataProvider(): array
    {
        $intervalResponse = [
            '2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z' => [
                'data_source_2019-08-19T14:00:00.000Z_2019-08-19T15:00:00.000Z' => [
                    'metadata' => [
                        'dataSource'    => 'dataSource',
                        'interval'      => '2017-01-01T00:00:00.000Z/2017-01-02T00:00:00.000Z',
                        'version'       => '2019-05-15T11:29:56.874Z',
                        'loadSpec'      => [],
                        'dimensions'    => 'country_iso,mccmnc,offer_id,product_type_id,promo_id',
                        'metrics'       => 'conversions,revenue',
                        'shardSpec'     => [],
                        'binaryVersion' => 9,
                        'size'          => 272709,
                        'identifier'    => 'traffic-conversions_2017-01-01T00:00:00.000Z_2017-01-02T00:00:00.000Z_2019-05-15T11:29:56.874Z',
                    ],
                    'servers'  => [
                        '172.31.23.160:8083',
                        '172.31.3.204:8083',
                    ],
                ],
            ],
        ];

        $metadataResponse = [
            [
                'id'        => 'traffic-conversions_2019-04-15T08:00:00.000Z_2019-04-15T09:00:00.000Z_2019-08-20T12:24:44.384Z',
                'intervals' => [
                    '2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z',
                ],
                'columns'   => [
                    '__time' => [
                        'type' => 'LONG',
                        'size' => 0,
                    ],
                ],
            ],
        ]

        return [
            ["first", $intervalResponse],
            ["LaSt"],
            ["2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z"],
        ];
    }

    /**
     * @param string $interval
     * @param array  $intervalResponse
     * @param array  $metadataResponse
     * @param        $expectException
     *
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @dataProvider structureDataProvider
     *
     */
    public function testStructure(string $interval, array $intervalResponse, array $metadataResponse, $expectException)
    {
        $intervals = [
            '2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z' => ['size' => 75208, 'count' => 4],
            '2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z' => ['size' => 161870, 'count' => 8],
        ];

        $druidClient     = Mockery::mock(DruidClient::class, [[]]);
        $metadataBuilder = Mockery::mock(MetadataBuilder::class, [$druidClient]);
        $queryBuilder    = Mockery::mock(QueryBuilder::class, [$druidClient, 'myDataSource']);

        $metadataBuilder->makePartial();

        if (in_array(strtolower($interval), ['first', 'last'])) {
            $metadataBuilder->shouldReceive('intervals')
                ->once()
                ->with('myDataSource')
                ->andReturn($intervals);

            $keys = array_keys($intervals);
            if ($interval == 'last') {
                $interval = $intervals[0];
            } else {
                $interval = $intervals[count($intervals) - 1];
            }
        }

        $metadataBuilder->shouldReceive('interval')
            ->once()
            ->with('myDataSource', $interval)
            ->andReturn($intervalResponse);

        $druidClient->shouldReceive('query')
            ->with('myDataSource')
            ->once()
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('interval')
            ->once()
            ->with($interval)
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('segmentMetadata')
            ->once()
            ->andReturn($metadataResponse);

        if ($expectException) {
            $this->expectException($expectException);
        }

        $response = $metadataBuilder->structure('myDataSource', $interval);
    }
}