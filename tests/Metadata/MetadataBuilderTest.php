<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Metadata;

use Mockery;
use Closure;
use Exception;
use Mockery\MockInterface;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Mockery\LegacyMockInterface;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\TimeBound;
use Level23\Druid\Metadata\Structure;
use GuzzleHttp\Client as GuzzleClient;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\Context\QueryContext;
use Level23\Druid\Filters\FilterBuilder;
use Level23\Druid\Metadata\MetadataBuilder;
use Level23\Druid\Context\ContextInterface;
use Level23\Druid\DataSources\TableDataSource;
use Level23\Druid\DataSources\DataSourceInterface;
use Level23\Druid\Exceptions\QueryResponseException;
use Level23\Druid\Responses\SegmentMetadataQueryResponse;

class MetadataBuilderTest extends TestCase
{
    protected QueryBuilder|MockInterface|LegacyMockInterface $client;

    protected function setUp(): void
    {
        $guzzle = new GuzzleClient(['base_uri' => 'https://httpbin.org']);

        $this->client = Mockery::mock(DruidClient::class, [[], $guzzle]);

        parent::setUp();
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException|\GuzzleHttp\Exception\GuzzleException
     */
    public function testIntervals(): void
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
     * @throws \Level23\Druid\Exceptions\QueryResponseException|\GuzzleHttp\Exception\GuzzleException
     */
    public function testInterval(): void
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

    /**
     * @return array<array<array<int|string,array<string,array<string,array<int|string,array<scalar>|int|string>>|int|string>>|Structure|string|null>>.
     */
    public static function structureDataProvider(): array
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
     * @throws \Level23\Druid\Exceptions\QueryResponseException|\GuzzleHttp\Exception\GuzzleException
     */
    public function testStructureWithEmptyInterval(): void
    {
        $druidClient = new DruidClient([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(' Maybe there are no intervals for this dataSource?');

        $druidClient->metadata()->structure('wikipedia', '');
    }

    /**
     * @param string                                                                                    $dataSource
     * @param string                                                                                    $interval
     * @param array<string,array<string,array<string,array<string[]|string,string|int|array<scalar>>>>> $intervalResponse
     * @param array<array<string,string|int>>                                                           $columnsResponse
     * @param \Level23\Druid\Metadata\Structure|null                                                    $expectedResponse
     *
     * @throws \Level23\Druid\Exceptions\QueryResponseException|\GuzzleHttp\Exception\GuzzleException
     * @dataProvider structureDataProvider
     */
    public function testStructure(
        string $dataSource,
        string $interval,
        array $intervalResponse,
        array $columnsResponse,
        ?Structure $expectedResponse
    ): void {

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
     * @param array<string,array<string,array<string,string>>> $segmentMetadataResponse
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testGetColumnsForInterval(array $segmentMetadataResponse): void
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
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testGetIntervalByShorthand(string $dataSource, string $shortHand): void
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

        $response = $metadataBuilder
            ->shouldAllowMockingProtectedMethods()
            ->getIntervalByShorthand($dataSource, $shortHand);

        if ($lowerShortHand == 'last') {
            $this->assertEquals('2019-08-19T14:00:00.000Z/2019-08-19T15:00:00.000Z', $response);
        } else {
            $this->assertEquals('2019-08-19T12:00:00.000Z/2019-08-19T13:00:00.000Z', $response);
        }
    }

    /**
     * @return array<array<string|TableDataSource|TimeBound|Closure|QueryContext|null>>
     */
    public static function dataProvider(): array
    {
        return [
            [
                'wikipedia',
                TimeBound::MAX_TIME,
                function (FilterBuilder $builder) {
                    $builder->where('name', '=', 'John Doe');
                },
            ],
            [
                'wikipedia',
                null,
                null,
                new QueryContext(['timeout' => '3000']),
            ],
            [
                'wikipedia',
                TimeBound::BOTH,
            ],
            [
                new TableDataSource('wikipedia'),
                'minTime',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testTimeBoundary(
        DataSourceInterface|string $dataSource,
        null|string|TimeBound $bound,
        Closure $filterBuilder = null,
        ContextInterface $context = null,
    ): void {
        $metadataBuilder = Mockery::mock(MetadataBuilder::class, [$this->client]);
        $metadataBuilder->makePartial();

        $expected = [
            'queryType'  => 'timeBoundary',
            'dataSource' => $dataSource instanceof DataSourceInterface ? $dataSource->toArray() : $dataSource,
        ];

        $expectedBound = $bound;
        if (is_string($expectedBound)) {
            $expectedBound = TimeBound::from($expectedBound);
        }

        if (!empty($expectedBound) && $expectedBound != TimeBound::BOTH) {
            $expected['bound'] = $expectedBound->value;
        }

        if ($filterBuilder) {
            $builder = new FilterBuilder();
            call_user_func($filterBuilder, $builder);
            $filter = $builder->getFilter();

            if ($filter) {
                $expected['filter'] = $filter->toArray();
            }
        }

        if ($context) {
            $expected['context'] = $context->toArray();
        }

        $this->client->shouldReceive('config')
            ->once()
            ->with('broker_url')
            ->andReturn('http://broker.url');

        $this->client->shouldReceive('executeRawRequest')
            ->once()
            ->withArgs(function ($method, $url, $config) use ($expected) {
                $this->assertEquals('post', $method);
                $this->assertEquals('http://broker.url/druid/v2', $url);
                $this->assertEquals($expected, $config);

                return true;
            })
            ->andReturn([
                [
                    'timestamp' => "2013-05-09T18:24:00.000Z",
                    "result"    => [
                        "minTime" => "2013-05-09T18:24:00.000Z",
                        "maxTime" => "2013-05-09T18:37:00.000Z",
                    ],
                ],
            ]);

        $metadataBuilder->timeBoundary(
            $dataSource,
            $bound,
            $filterBuilder,
            $context
        );
    }

    /**
     * @return array<int, array<int,array<string, array<string, string>|string>|string>>
     */
    public static function responseDataProvider(): array
    {
        return [
            [
                [
                    'timestamp' => "2013-05-09T18:24:00.000Z",
                    "result"    => [
                        "minTime" => "2013-05-09T18:24:00.000Z",
                        "maxTime" => "2013-05-09T18:37:00.000Z",
                    ],
                ],
            ],
            [
                [
                    'timestamp' => "2013-05-09T18:24:00.000Z",
                    "result"    => [
                        "minTime" => "wrong",
                        "maxTime" => "2013-05-09T18:37:00.000Z",
                    ],
                ],
                'Failed to parse time: wrong',
            ],
            [
                [
                    'timestamp' => "2013-05-09T18:24:00.000Z",
                    "result"    => [
                        "minTime" => "2013-05-09T18:24:00.000Z",
                    ],
                ],
            ],
            [
                [
                    'timestamp' => "2013-05-09T18:24:00.000Z",
                    "result"    => [
                        "maxTime" => "2013-05-09T18:24:00.000Z",
                    ],
                ],
            ],
            [
                [
                    'timestamp' => "2013-05-09T18:24:00.000Z",
                    "result"    => [
                        "maxTime" => "wrong",
                    ],
                ],
                'Failed to parse time: wrong',
            ],
            [
                [],
                'Received incorrect response:',
            ],

        ];
    }

    /**
     * @dataProvider responseDataProvider
     *
     * @param array<int,null|array<string,string[]|string>> $response
     * @param string|null                                   $exceptionMessage
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testTimeBoundaryResponse(array $response, string $exceptionMessage = null): void
    {
        $metadataBuilder = Mockery::mock(MetadataBuilder::class, [$this->client]);
        $metadataBuilder->makePartial();

        $this->client->shouldReceive('config')
            ->once()
            ->with('broker_url')
            ->andReturn('http://broker.url');

        if (!empty($exceptionMessage)) {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage($exceptionMessage);
        }

        $this->client->shouldReceive('executeRawRequest')
            ->once()
            ->withArgs(function ($method, $url, $config) {
                $expected = [
                    'queryType'  => 'timeBoundary',
                    'dataSource' => 'wikipedia',
                ];
                $this->assertEquals('post', $method);
                $this->assertEquals('http://broker.url/druid/v2', $url);
                $this->assertEquals($expected, $config);

                return true;
            })
            ->andReturn([$response]);

        $metadataBuilder->timeBoundary('wikipedia');
    }

    public function testDataSources(): void
    {
        $metadataBuilder = Mockery::mock(MetadataBuilder::class, [$this->client]);
        $metadataBuilder->makePartial();

        $this->client->shouldReceive('config')
            ->once()
            ->with('coordinator_url')
            ->andReturn('https://coordinator.url');

        $this->client->shouldReceive('executeRawRequest')
            ->once()
            ->with('get', 'https://coordinator.url/druid/coordinator/v1/datasources')
            ->andReturn(['wikipedia', 'clicks']);

        $response = $metadataBuilder->dataSources();

        $this->assertEquals(['wikipedia', 'clicks'], $response);
    }

}
