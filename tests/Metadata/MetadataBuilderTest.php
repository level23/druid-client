<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Metadata;

use Mockery;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use tests\Level23\Druid\TestCase;
use Level23\Druid\Metadata\Structure;
use GuzzleHttp\Client as GuzzleClient;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\Metadata\MetadataBuilder;
use Level23\Druid\Exceptions\QueryResponseException;
use Level23\Druid\Responses\SegmentMetadataQueryResponse;

class MetadataBuilderTest extends TestCase
{
    /**
     * @var \Level23\Druid\DruidClient|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $client;

    protected function setUp(): void
    {
        $guzzle = new GuzzleClient(['base_uri' => 'http://httpbin.org']);

        $this->client = Mockery::mock(DruidClient::class, [[], $guzzle]);

        parent::setUp();
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testIntervals()
    {
        $intervals = [
            "2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z" => ["size" => 75208, "count" => 4],
            "2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z" => ["size" => 161870, "count" => 8],
        ];

        $this->client->shouldReceive('config')
            ->once()
            ->with('coordinator_url')
            ->andReturn('http://coordinator.url');

        $this->client->shouldReceive('executeRawRequest')
            ->once()
            ->with('get',
                'http://coordinator.url/druid/coordinator/v1/datasources/' . urlencode('dataSource') . '/intervals',
                ['simple' => '']
            )
            ->andReturn($intervals);

        $builder  = new MetadataBuilder($this->client);
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
        $builder = new MetadataBuilder($this->client);

        $intervalResponse = ['druid' => ['response' => 'here']];

        $this->client->shouldReceive('config')
            ->once()
            ->with('coordinator_url')
            ->andReturn('http://coordinator.url');

        $this->client->shouldReceive('executeRawRequest')
            ->once()
            ->with('get',
                'http://coordinator.url/druid/coordinator/v1/datasources/' . urlencode('dataSource') .
                '/intervals/' . urlencode('2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z'),
                ['full' => '']
            )
            ->andReturn($intervalResponse);

        $response = $builder->interval(
            'dataSource',
            '2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z'
        );

        $this->assertEquals($intervalResponse, $response);
    }

    public function structureDataProvider(): array
    {
        $dataSource = 'myDataSource';

        $intervalResponse = [
            '2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z' => [
                'data_source_2019-08-19T14:00:00.000Z_2019-08-19T15:00:00.000Z' => [
                    'metadata' => [
                        'dataSource'    => $dataSource,
                        'interval'      => '2017-01-01T00:00:00.000Z/2017-01-02T00:00:00.000Z',
                        'version'       => '2019-05-15T11:29:56.874Z',
                        'loadSpec'      => [],
                        'dimensions'    => 'country_iso',
                        'metrics'       => 'revenue',
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

        $columnsResponse = [
            [
                'field'        => '__time',
                'type'         => 'LONG',
                'size'         => 0,
                'cardinality'  => 84,
                'minValue'     => '',
                'maxValue'     => 74807,
                'errorMessage' => '',
            ],
            [
                'field'        => 'country_iso',
                'type'         => 'STRING',
                'size'         => 0,
                'cardinality'  => 4,
                'minValue'     => '',
                'maxValue'     => '',
                'errorMessage' => '',
            ],
            [
                'field'        => 'revenue',
                'type'         => 'DOUBLE',
                'size'         => 0,
                'cardinality'  => 84,
                'minValue'     => '',
                'maxValue'     => 74807,
                'errorMessage' => '',
            ],
        ];

        $structure = new Structure(
            $dataSource,
            ['country_iso' => 'STRING'],
            ['revenue' => 'DOUBLE']
        );

        return [
            [
                $dataSource,
                'first',
                $intervalResponse,
                $columnsResponse,
                $structure,
            ],
            [
                $dataSource,
                'LaSt',
                $intervalResponse,
                $columnsResponse,
                $structure,
            ],
            [
                $dataSource,
                '2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z',
                $intervalResponse,
                $columnsResponse,
                $structure,
            ],
            [
                $dataSource,
                '2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z',
                [],
                $columnsResponse,
                null,
                null,
            ],
            [
                $dataSource,
                '2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z',
                ['item' => []],
                $columnsResponse,
                null,
            ],
        ];
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testStructureWithEmptyInterval()
    {
        $druidClient = new DruidClient([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(' Maybe there are no intervals for this dataSource?');

        $druidClient->metadata()->structure('wikipedia', '');
    }

    /**
     * @param string                            $dataSource
     * @param string                            $interval
     * @param array                             $intervalResponse
     * @param array                             $columnsResponse
     * @param \Level23\Druid\Metadata\Structure $expectedResponse
     *
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @dataProvider structureDataProvider
     */
    public function testStructure(
        string $dataSource,
        string $interval,
        array $intervalResponse,
        array $columnsResponse,
        $expectedResponse
    ) {

        $metadataBuilder = Mockery::mock(MetadataBuilder::class, [$this->client]);

        $metadataBuilder->makePartial();

        if (in_array(strtolower($interval), ['first', 'last'])) {
            $metadataBuilder->shouldAllowMockingProtectedMethods()
                ->shouldReceive('getIntervalByShorthand')
                ->once()
                ->with($dataSource, $interval)
                ->andReturn('2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z');

            $expectedInterval = '2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z';
        } else {
            $expectedInterval = $interval;
        }

        $metadataBuilder->shouldReceive('interval')
            ->once()
            ->with($dataSource, $expectedInterval)
            ->andReturn($intervalResponse);

        $exception = false;

        $intervalResponse = reset($intervalResponse);
        if (!$intervalResponse) {
            $this->expectException(QueryResponseException::class);
            $exception = true;
        }

        if (!$exception) {
            $metadataBuilder->shouldAllowMockingProtectedMethods()
                ->shouldReceive('getColumnsForInterval')
                ->once()
                ->with($dataSource, $expectedInterval)
                ->andReturn($columnsResponse);
        }

        $response = $metadataBuilder->structure($dataSource, $interval);

        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * @testWith [[{"columns": {"__time": {"type":"LONG"}}}]]
     *
     * @param array $segmentMetadataResponse
     */
    public function testGetColumnsForInterval(array $segmentMetadataResponse)
    {
        $dataSource      = 'myDataSource';
        $interval        = '2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z';
        $metadataBuilder = Mockery::mock(MetadataBuilder::class, [$this->client]);
        $metadataBuilder->makePartial();
        $queryBuilder = Mockery::mock(QueryBuilder::class, [$this->client, 'myDataSource']);

        $this->client->shouldReceive('query')
            ->with($dataSource)
            ->once()
            ->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('interval')
            ->once()
            ->with($interval)
            ->andReturn($queryBuilder);

        $responseObj = new SegmentMetadataQueryResponse($segmentMetadataResponse);

        $queryBuilder->shouldReceive('segmentMetadata')
            ->once()
            ->andReturn($responseObj);

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $metadataBuilder
            ->shouldAllowMockingProtectedMethods()
            ->getColumnsForInterval($dataSource, $interval);

        $this->assertEquals($responseObj->data(), $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @testWith ["dataSource", "laST"]
     *           ["theDataSource", "first"]
     *           ["john", "wrong"]
     *
     * @param string $dataSource
     * @param string $shortHand
     */
    public function testGetIntervalByShorthand(string $dataSource, string $shortHand)
    {
        $metadataBuilder = Mockery::mock(MetadataBuilder::class, [$this->client]);
        $metadataBuilder->makePartial();

        $intervals = [
            '2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z' => ['size' => 75208, 'count' => 4],
            '2019-08-19T13:00:00.000Z/2019-08-19T14:00:00.000Z' => ['size' => 161870, 'count' => 8],
            '2019-08-19T12:00:00.000Z/2019-08-19T13:00:00.000Z' => ['size' => 161870, 'count' => 8],
        ];

        $lowerShortHand = strtolower($shortHand);
        if ($lowerShortHand != 'first' && $lowerShortHand != 'last') {
            $this->expectException(InvalidArgumentException::class);
        } else {
            $metadataBuilder->shouldReceive('intervals')
                ->once()
                ->with($dataSource)
                ->andReturn($intervals);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $response = $metadataBuilder
            ->shouldAllowMockingProtectedMethods()
            ->getIntervalByShorthand($dataSource, $shortHand);

        if ($lowerShortHand == 'last') {
            $this->assertEquals('2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z', $response);
        } else {
            $this->assertEquals('2019-08-19T12:00:00.000Z/2019-08-19T13:00:00.000Z', $response);
        }
    }
}
