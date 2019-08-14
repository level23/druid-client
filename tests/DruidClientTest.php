<?php
declare(strict_types=1);

namespace tests\Level23\Druid;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Hamcrest\Type\IsArray;
use Level23\Druid\DruidClient;
use Level23\Druid\Exceptions\DruidCommunicationException;
use Level23\Druid\Exceptions\DruidQueryException;
use Level23\Druid\Queries\GroupByQuery;
use Level23\Druid\QueryBuilder;
use Mockery;
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
        $this->expectException(\InvalidArgumentException::class);
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
        $counter    = 0;
        $logHandler = function ($message) use (&$counter) {
            $this->assertEquals($message, 'something');
            $counter++;
        };

        $client = Mockery::mock(DruidClient::class, [[], $logHandler]);
        $client->makePartial();
        /** @noinspection PhpUndefinedMethodInspection */
        $client->shouldAllowMockingProtectedMethods()->log('something');

        $this->assertEquals(1, $counter);
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
            ],
            // Test incorrect http code
            [
                $response,
                function () use ($response) {
                    return new GuzzleResponse(500, [], (string)json_encode($response));
                },
                DruidQueryException::class,
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
                DruidCommunicationException::class,
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
                DruidCommunicationException::class,
            ],
            // Test a generic Exception
            [
                $response,
                function () {
                    throw new \Exception('Something went wrong!');
                },
                DruidQueryException::class,
            ],

        ];
    }

    /**
     * @dataProvider executeQueryDataProvider
     *
     * @param callable $responseFunction
     * @param mixed    $expectException
     *
     * @throws \Level23\Druid\Exceptions\DruidException
     * @throws \Level23\Druid\Exceptions\DruidQueryException
     */
    public function testExecuteDruidQuery(array $response, callable $responseFunction, $expectException)
    {
        $queryArray = ['something' => 'here'];

        $config = [
            'broker_url'     => 'http://test.dev',
            'guzzle_options' => ['strict' => false],
        ];

        if ($expectException) {
            $this->expectException($expectException);
            $this->expectExceptionCode(0);
        }

        $query = Mockery::mock(GroupByQuery::class);
        $query->shouldReceive('getQuery')
            ->once()
            ->andReturn($queryArray);

        $guzzle = Mockery::mock(GuzzleClient::class);
        $guzzle->shouldReceive('post')
            ->once()
            ->with(
                $config['broker_url'] . '/druid/v2',
                new IsArray()
            )
            ->andReturnUsing(function ($url, $options) use ($responseFunction, $queryArray) {
                $this->assertIsArray($options);

                $this->assertEquals([
                    'timeout'         => 60,
                    'allow_redirects' => true,
                    'connect_timeout' => 10,

                    'headers' => [
                        'Content-Type' => 'application/json',
                        'User-Agent'   => 'level23 druid client package',
                    ],
                    'strict'  => false,
                    'body'    => json_encode($queryArray, JSON_PRETTY_PRINT),
                ], $options);

                $response = call_user_func($responseFunction, $url, $options);

                return $response;
            });

        $client = Mockery::mock(DruidClient::class, [$config]);
        $client->makePartial();

        $expected = ['retries' => 2];

        $this->assertArrayContainsSubset($expected, $client->getConfig());

        $client->setGuzzleClient($guzzle);

        $client->shouldAllowMockingProtectedMethods();


        $druidResult = $client->executeDruidQuery($query);

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