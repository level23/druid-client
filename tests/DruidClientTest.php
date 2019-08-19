<?php
declare(strict_types=1);

namespace tests\Level23\Druid;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Hamcrest\Type\IsArray;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Exceptions\QueryResponseException;
use Level23\Druid\Queries\GroupByQuery;
use Level23\Druid\QueryBuilder;
use Mockery;
use Psr\Log\LoggerInterface;
use tests\TestCase;

class DruidClientTest extends TestCase
{
    /**
     * @var \Level23\Druid\DruidClient
     */
    protected $client;

    public function setUp(): void
    {
        $this->client = new DruidClient([]);
    }

    public function testInvalidGranularity()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The given granularity is invalid');

        $this->client->query('hits', 'fout');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testQuery()
    {
        $builder = Mockery::mock('overload:' . QueryBuilder::class);
        $builder->shouldReceive('__construct')
            ->once()
            ->with($this->client, 'randomDataSource', 'quarter');

        $this->client->query('randomDataSource', 'quarter');
    }

    public function testLogHandler()
    {
        $client = Mockery::mock(DruidClient::class, [[]]);
        $client->makePartial();

        $logger = Mockery::mock(LoggerInterface::class);
        $logger->shouldReceive('info')->once();

        $client->setLogger($logger);

        /** @noinspection PhpUndefinedMethodInspection */
        $client->shouldAllowMockingProtectedMethods()->log('something');
    }

    public function executeQueryDataProvider(): array
    {
        $response = [
            'event' => [
                'name' => 'john',
                'cars' => 12,
            ],
        ];

        return [
            // Correct response
            [
                $response,
                function () use ($response) {
                    return new GuzzleResponse(200, [], (string)json_encode($response));
                },
                false,
                false,
            ],
            // Test incorrect http code
            [
                $response,
                function () {
                    throw new ServerException(
                        'Unknow exception',
                        new GuzzleRequest('GET', '/druid/v1', [], ''),
                        new GuzzleResponse(500, [], (string)json_encode([
                            'error'        => 'Unknown exception',
                            'errorMessage' => 'Some query error',
                        ]))
                    );
                },
                QueryResponseException::class,
                500,
            ],
            // Test a BadResponseException
            [
                $response,
                function () {
                    throw new BadResponseException(
                        'Bad response',
                        new GuzzleRequest('GET', '/druid/v1', [], '')
                    );
                },
                BadResponseException::class,
                0,
            ],
            // Test a RequestException
            [
                $response,
                function () {
                    throw new RequestException(
                        'Request exception',
                        new GuzzleRequest('GET', '/druid/v1', [], '')
                    );
                },
                RequestException::class,
                0,
            ],
            // Test a generic Exception
            [
                $response,
                function () {
                    throw new Exception('Something went wrong!');
                },
                Exception::class,
                0,
            ],

        ];
    }

    /**
     * @dataProvider executeQueryDataProvider
     *
     * @param array    $response
     * @param callable $responseFunction
     * @param mixed    $expectException
     * @param int      $exceptionCode
     *
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testExecuteDruidQuery(array $response, callable $responseFunction, $expectException, $exceptionCode)
    {
        $queryArray = ['something' => 'here'];

        $config = [
            'broker_url'     => 'http://test.dev',
            'guzzle_options' => ['strict' => false],
        ];

        if ($expectException) {
            $this->expectException($expectException);
            $this->expectExceptionCode($exceptionCode);
        }

        $query = Mockery::mock(GroupByQuery::class);
        $query->shouldReceive('toArray')
            ->once()
            ->andReturn($queryArray);

        $guzzle = Mockery::mock(GuzzleClient::class);
        $guzzle->shouldReceive('post')
            ->once()
            ->with(
                'http://test.dev/druid/v2',
                new IsArray()
            )
            ->andReturnUsing(function ($url, $options) use ($responseFunction, $queryArray) {
                $this->assertIsArray($options);

                $this->assertEquals(['json' => $queryArray,], $options);

                $response = call_user_func($responseFunction, $url, $options);

                return $response;
            });

        $client = Mockery::mock(DruidClient::class, [$config, $guzzle]);
        $client->makePartial();

        $client->shouldAllowMockingProtectedMethods();

        $druidResult = $client->executeQuery($query);

        if (!$expectException) {
            $this->assertEquals($response, $druidResult);
        }
    }

    //    public function testOptions()
    //    {
    //
    //    }
    //
    //    public function testGetEventData()
    //    {
    //        $client = Mockery::mock(DruidClient::class, []);
    //
    //        //$this->assertEquals($client->getConfig());
    //    }
}