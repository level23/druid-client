<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Lookups;

use Mockery;
use DateTime;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Lookups\MapLookup;
use Level23\Druid\Lookups\UriLookup;
use Level23\Druid\Lookups\JdbcLookup;
use Level23\Druid\Lookups\KafkaLookup;
use Level23\Druid\Lookups\LookupBuilder;
use Level23\Druid\Lookups\UriPrefixLookup;
use Level23\Druid\Lookups\ParseSpecs\CsvParseSpec;
use Level23\Druid\Lookups\ParseSpecs\TsvParseSpec;
use Level23\Druid\Lookups\ParseSpecs\ParseSpecInterface;
use Level23\Druid\Lookups\ParseSpecs\CustomJsonParseSpec;
use Level23\Druid\Lookups\ParseSpecs\SimpleJsonParseSpec;

class LookupBuilderTest extends TestCase
{
    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testStoreWithoutLookup(): void
    {
        $client = new DruidClient([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No lookup defined. Please define the lookup by using the map, kafka, jdbc, uri or uriPrefix methods!');

        $client->lookup()->store('samples');
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testStoreWithoutParseSpec(): void
    {
        $client = new DruidClient([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Using an URI lookup, but there is no parseSpec defined! Use the csv, tsv, simpleJson or customJson methods to define the parseSpec.');

        $client->lookup()->uri('/path/to/file.json')->store('file_names');
    }

    /**
     * @testWith ["countries"]
     *           ["operators", "mobile"]
     *           ["countries", null, "v1"]
     *
     * @param string      $lookupName
     * @param string|null $tier
     * @param string|null $version
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testStoreUri(string $lookupName, ?string $tier = null, ?string $version = null): void
    {
        $client = Mockery::mock(DruidClient::class);

        $lookup = new UriLookup(
            new SimpleJsonParseSpec(),
            '/path/to/countries.json',
            'PT30M',
            10,
            true,
            60000
        );

        $client->shouldReceive('config')
            ->once()
            ->with('coordinator_url')
            ->andReturn('https://coordinator.example.com');

        $client->shouldReceive('executeRawRequest')
            ->once()
            ->with(
                'post',
                'https://coordinator.example.com/druid/coordinator/v1/lookups/config/' . ($tier ?? '__default') . '/' . $lookupName,
                [
                    'version'                => $version ?? (new DateTime())->format('Y-m-d\TH:i:s.000\Z'),
                    'lookupExtractorFactory' => $lookup->toArray(),
                ]
            );

        $builder = new LookupBuilder($client);

        $builder = $builder->uri('/path/to/countries.json')
            ->json()
            ->pollPeriod('PT30M')
            ->maxHeapPercentage(10)
            ->injective()
            ->firstCacheTimeout(60000);

        if ($tier) {
            $builder->store($lookupName, $tier, $version);
        } else {
            $builder->store(
                lookupName: $lookupName,
                versionName: $version
            );
        }
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testStoreJdbc(): void
    {
        $client = Mockery::mock(DruidClient::class);

        $lookup = new JdbcLookup(
            'jdbc:mysql://localhost:3306/druid',
            'druid',
            'pssswrrdd',
            'users',
            'id',
            'name',
            "state='active'",
            'updated_at',
            30,
            30,
            'PT30M',
            20,
            false,
            15000
        );

        $client->shouldReceive('config')
            ->once()
            ->with('coordinator_url')
            ->andReturn('https://coordinator.example.com');

        $client->shouldReceive('executeRawRequest')
            ->once()
            ->with(
                'post',
                'https://coordinator.example.com/druid/coordinator/v1/lookups/config/company/usernames',
                [
                    'version'                => (new DateTime())->format('Y-m-d\TH:i:s.000\Z'),
                    'lookupExtractorFactory' => $lookup->toArray(),
                ]
            );

        $builder = new LookupBuilder($client);

        $builder->jdbc(
                connectUri: 'jdbc:mysql://localhost:3306/druid',
                username: 'druid',
                password: 'pssswrrdd',
                table: 'users',
                keyColumn: 'id',
                valueColumn: 'name',
                filter: "state='active'",
                tsColumn: 'updated_at',
            )
            ->pollPeriod('PT30M')
            ->maxHeapPercentage(20)
            ->injective(false)
            ->firstCacheTimeout(15000)
            ->store(
                lookupName: 'usernames',
                tier: 'company'
            );
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testStoreKafka(): void
    {
        $client = Mockery::mock(DruidClient::class);

        $lookup = new KafkaLookup(
            'clients',
            'kafka.service:9092',
            ['group.id' => 'myApp'],
            30,
            true
        );

        $client->shouldReceive('config')
            ->once()
            ->with('coordinator_url')
            ->andReturn('https://coordinator.example.com');

        $client->shouldReceive('executeRawRequest')
            ->once()
            ->with(
                'post',
                'https://coordinator.example.com/druid/coordinator/v1/lookups/config/__default/client_names',
                [
                    'version'                => (new DateTime())->format('Y-m-d\TH:i:s.000\Z'),
                    'lookupExtractorFactory' => $lookup->toArray(),
                ]
            );

        $builder = new LookupBuilder($client);

        $builder
            ->kafka(
                'clients',
                'kafka.service:9092',
                ['group.id' => 'myApp'],
                30
            )
            ->injective()
            ->store(
                lookupName: 'client_names'
            );
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testStoreMap(): void
    {
        $client = Mockery::mock(DruidClient::class);

        $lookup = new MapLookup(
            ['foo' => 'bar', 'zoo' => 'baz'],
        );

        $client->shouldReceive('config')
            ->once()
            ->with('coordinator_url')
            ->andReturn('https://coordinator.example.com');

        $client->shouldReceive('executeRawRequest')
            ->once()
            ->with(
                'post',
                'https://coordinator.example.com/druid/coordinator/v1/lookups/config/__default/foo_bar',
                [
                    'version'                => (new DateTime())->format('Y-m-d\TH:i:s.000\Z'),
                    'lookupExtractorFactory' => $lookup->toArray(),
                ]
            );

        $builder = new LookupBuilder($client);

        $builder
            ->map(['foo' => 'bar', 'zoo' => 'baz'])
            ->store(
                lookupName: 'foo_bar'
            );
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testKeys(): void
    {
        $builder = Mockery::mock(LookupBuilder::class);
        $builder->makePartial();

        $builder->shouldReceive('introspect')
            ->once()
            ->with('countries')
            ->andReturn([
                'nl' => 'The Netherlands',
                'de' => 'Germany',
                'be' => 'Belgium',
            ]);

        $response = $builder->keys('countries');

        $this->assertEquals(['nl', 'de', 'be'], $response);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testValues(): void
    {
        $builder = Mockery::mock(LookupBuilder::class);
        $builder->makePartial();

        $builder->shouldReceive('introspect')
            ->once()
            ->with('countries')
            ->andReturn([
                'nl' => 'The Netherlands',
                'de' => 'Germany',
                'be' => 'Belgium',
            ]);

        $response = $builder->values('countries');

        $this->assertEquals(['The Netherlands', 'Germany', 'Belgium'], $response);
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testIntrospect(): void
    {
        $data = [
            'nl' => 'The Netherlands',
            'de' => 'Germany',
            'be' => 'Belgium',
        ];

        $client = Mockery::mock(DruidClient::class);

        $builder = new LookupBuilder($client);

        $client->shouldReceive('config')
            ->once()
            ->with('broker_url')
            ->andReturn('https://broker.example.com');

        $client->shouldReceive('executeRawRequest')
            ->once()
            ->with('get', 'https://broker.example.com/druid/v1/lookups/introspect/countries')
            ->andReturn($data);

        $response = $builder->introspect('countries');

        $this->assertEquals($data, $response);
    }

    /**
     * @testWith ["countries", "globeData"]
     *           ["titles"]
     * @param string      $lookupName
     * @param string|null $tier
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testDelete(string $lookupName, string|null $tier = null): void
    {
        $client = Mockery::mock(DruidClient::class);

        $client->shouldReceive('config')
            ->once()
            ->with('coordinator_url')
            ->andReturn('https://coordinator.example.com');

        $client->shouldReceive('executeRawRequest')
            ->once()
            ->with('delete',
                'https://coordinator.example.com/druid/coordinator/v1/lookups/config/' . ($tier ?? '__default') . '/' . $lookupName);

        $builder = new LookupBuilder($client);
        if ($tier) {
            $builder->delete($lookupName, $tier);
        } else {
            $builder->delete($lookupName);
        }
    }

    /**
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testAll(): void
    {
        $all    = [
            '__default' =>
                [
                    'test_map'  =>
                        [
                            'version'                => '2024-10-14T15:16:55.000Z',
                            'lookupExtractorFactory' =>
                                [
                                    'type' => 'map',
                                    'map'  =>
                                        [
                                            'test1' => 'Test Number 1',
                                            'test2' => 'Test Number 2',
                                            'test3' => 'Test Number 3',
                                        ],
                                ],
                        ],
                    'usernames' =>
                        [
                            'version'                => '2024-10-15T11:21:30.000Z',
                            'lookupExtractorFactory' =>
                                [
                                    'type'                => 'cachedNamespace',
                                    'extractionNamespace' =>
                                        [
                                            'type'            => 'jdbc',
                                            'connectorConfig' =>
                                                [
                                                    'connectURI' => 'jdbc:mysql://database.example.com:3306/my_db_name',
                                                    'user'       => 'userN4me',
                                                    'password'   => 'p4ssw0rd!',
                                                ],
                                            'table'           => 'users',
                                            'keyColumn'       => 'id',
                                            'valueColumn'     => 'username',
                                            'filter'          => 'status = "active"',
                                            'pollPeriod'      => 'P15M',
                                            'jitterSeconds'   => 300,
                                        ],
                                    'injective'           => false,
                                    'firstCacheTimeout'   => 0,
                                ],
                        ],
                ],
        ];
        $client = Mockery::mock(DruidClient::class);

        $builder = new LookupBuilder($client);

        $client->shouldReceive('config')
            ->once()
            ->with('coordinator_url')
            ->andReturn('https://coordinator.example.com');

        $client->shouldReceive('executeRawRequest')
            ->once()
            ->with('get', 'https://coordinator.example.com/druid/coordinator/v1/lookups/config/all')
            ->andReturn($all);

        $response = $builder->all();

        $this->assertEquals($all, $response);
    }

    /**
     * @testWith [true]
     *           [false]
     *
     * @param bool $discover
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testTiers(bool $discover): void
    {
        $data   = [
            '__default',
            'tier1',
            'tier2',
        ];
        $client = Mockery::mock(DruidClient::class);

        $builder = new LookupBuilder($client);

        $client->shouldReceive('config')
            ->once()
            ->with('coordinator_url')
            ->andReturn('https://coordinator.example.com');

        $client->shouldReceive('executeRawRequest')
            ->once()
            ->with('get',
                'https://coordinator.example.com/druid/coordinator/v1/lookups/config?discover=' . ($discover ? 'true' : 'false'))
            ->andReturn($data);

        $response = $builder->tiers($discover);

        $this->assertEquals($data, $response);
    }

    /**
     * @throws \ReflectionException
     */
    public function testFirstCacheTimeout(): void
    {
        $client  = new DruidClient([]);
        $builder = $client->lookup();

        $this->assertEquals(0, $this->getProperty($builder, 'firstCacheTimeoutMs'));

        $result = $builder->firstCacheTimeout(1200);
        $this->assertEquals($result, $builder);

        $this->assertEquals(1200, $this->getProperty($builder, 'firstCacheTimeoutMs'));
    }

    /**
     * @throws \ReflectionException
     */
    public function testInjective(): void
    {
        $client  = new DruidClient([]);
        $builder = $client->lookup();

        $this->assertFalse($this->getProperty($builder, 'injective'));

        $result = $builder->injective();
        $this->assertEquals($result, $builder);
        $this->assertTrue($this->getProperty($builder, 'injective'));

        $builder->injective(false);
        $this->assertFalse($this->getProperty($builder, 'injective'));
    }

    /**
     * @throws \ReflectionException
     */
    public function testPollPeriod(): void
    {
        $client  = new DruidClient([]);
        $builder = $client->lookup();

        $this->assertEquals(null, $this->getProperty($builder, 'pollPeriod'));

        $result = $builder->pollPeriod(60000);
        $this->assertEquals($result, $builder);
        $this->assertEquals(60000, $this->getProperty($builder, 'pollPeriod'));

        $builder->pollPeriod('PT15M');
        $this->assertEquals('PT15M', $this->getProperty($builder, 'pollPeriod'));
    }

    /**
     * @throws \ReflectionException
     */
    public function testMaxHeapPercentage(): void
    {
        $client  = new DruidClient([]);
        $builder = $client->lookup();

        $this->assertEquals(null, $this->getProperty($builder, 'maxHeapPercentage'));

        $percentage = rand(1, 99);
        $result     = $builder->maxHeapPercentage($percentage);
        $this->assertEquals($result, $builder);
        $this->assertEquals($percentage, $this->getProperty($builder, 'maxHeapPercentage'));
    }

    /**
     * @testWith ["__default"]
     *           []
     *           ["production"]
     *
     * @param string|null $tier
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testNames(?string $tier = null): void
    {
        $data   = [
            'usernames',
            'countries',
        ];
        $client = Mockery::mock(DruidClient::class);

        $builder = new LookupBuilder($client);

        $client->shouldReceive('config')
            ->once()
            ->with('coordinator_url')
            ->andReturn('https://coordinator.example.com');

        $client->shouldReceive('executeRawRequest')
            ->once()
            ->with('get',
                'https://coordinator.example.com/druid/coordinator/v1/lookups/config/' . ($tier ?? '__default'))
            ->andReturn($data);

        $response = $tier ? $builder->names($tier) : $builder->names();

        $this->assertEquals($data, $response);
    }

    /**
     * @testWith ["test_map", "__default"]
     *           ["countries"]
     *
     * @param string      $name
     * @param string|null $tier
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function testGet(string $name, ?string $tier = null): void
    {
        $data   = [
            'version'                => '2024-10-14T15:16:55.000Z',
            'lookupExtractorFactory' =>
                [
                    'type' => 'map',
                    'map'  =>
                        [
                            'test1' => 'Test Nummer 1',
                            'test2' => 'Test Nummer 2',
                            'test3' => 'Test Nummer 3',
                        ],
                ],
        ];
        $client = Mockery::mock(DruidClient::class);

        $builder = new LookupBuilder($client);

        $client->shouldReceive('config')
            ->once()
            ->with('coordinator_url')
            ->andReturn('https://coordinator.example.com');

        $client->shouldReceive('executeRawRequest')
            ->once()
            ->with('get',
                'https://coordinator.example.com/druid/coordinator/v1/lookups/config/' . ($tier ?? '__default') . '/' . $name)
            ->andReturn($data);

        $response = $tier ? $builder->get($name, $tier) : $builder->get($name);

        $this->assertEquals($data, $response);
    }

    /**
     * @throws \ReflectionException
     */
    public function testMap(): void
    {
        $client  = new DruidClient([]);
        $builder = $client->lookup();

        $data   = ['foo' => 'FooBar', 'bar' => 'BarBaz'];
        $result = $builder->map($data);

        $this->assertEquals($result, $builder);

        $this->assertEquals(MapLookup::class, $this->getProperty($builder, 'lookupClass'));
        $this->assertEquals([$data], $this->getProperty($builder, 'parameters'));
    }

    /**
     * @throws \ReflectionException
     */
    public function testKafka(): void
    {
        $client  = new DruidClient([]);
        $builder = $client->lookup();

        $result = $builder->kafka(
            'users',
            'kafka.server1:9092,kafka.server2.9092',
            ['group.id' => 'myApp'],
            30
        );

        $this->assertEquals($result, $builder);

        $this->assertEquals(KafkaLookup::class, $this->getProperty($builder, 'lookupClass'));
        $this->assertEquals([
            'users',
            'kafka.server1:9092,kafka.server2.9092',
            ['group.id' => 'myApp'],
            30,
        ], $this->getProperty($builder, 'parameters'));

        $builder->kafka(
            'groups',
            ['kafka12.server:9092', 'kafka13.server.9092']
        );

        $this->assertEquals(KafkaLookup::class, $this->getProperty($builder, 'lookupClass'));
        $this->assertEquals([
            'groups',
            ['kafka12.server:9092', 'kafka13.server.9092'],
            [],
            0,
        ], $this->getProperty($builder, 'parameters'));
    }

    /**
     * @throws \ReflectionException
     */
    public function testJdbc(): void
    {
        $client  = new DruidClient([]);
        $builder = $client->lookup();

        $result = $builder->jdbc(
            'jdbc:mysql://localhost:3306/my_database',
            null,
            null,
            'users',
            'id',
            'username',
            "state='active'",
            'updated_at',
            300,
            30
        );

        $this->assertEquals($result, $builder);

        $this->assertEquals(JdbcLookup::class, $this->getProperty($builder, 'lookupClass'));
        $this->assertEquals([
            'jdbc:mysql://localhost:3306/my_database',
            null,
            null,
            'users',
            'id',
            'username',
            "state='active'",
            'updated_at',
            300,
            30,
        ], $this->getProperty($builder, 'parameters'));

        $builder->jdbc(
            'jdbc:mysql://localhost:3306/other_db',
            'pietertje',
            'PAARSWOORD',
            'countries',
            'id',
            'title',
        );

        $this->assertEquals(JdbcLookup::class, $this->getProperty($builder, 'lookupClass'));
        $this->assertEquals([
            'jdbc:mysql://localhost:3306/other_db',
            'pietertje',
            'PAARSWOORD',
            'countries',
            'id',
            'title',
            null,
            null,
            null,
            null,
        ], $this->getProperty($builder, 'parameters'));
    }

    /**
     * @throws \ReflectionException
     */
    public function testUri(): void
    {
        $client  = new DruidClient([]);
        $builder = $client->lookup();

        $result = $builder->uri('/var/mount/files/content.json');
        $this->assertEquals($result, $builder);

        $this->assertEquals(UriLookup::class, $this->getProperty($builder, 'lookupClass'));
        $this->assertEquals([
            '/var/mount/files/content.json',
        ], $this->getProperty($builder, 'parameters'));
    }

    /**
     * @throws \ReflectionException
     */
    public function testUriPrefix(): void
    {
        $client  = new DruidClient([]);
        $builder = $client->lookup();

        $result = $builder->uriPrefix('/var/mount/files/');
        $this->assertEquals($result, $builder);

        $this->assertEquals(UriPrefixLookup::class, $this->getProperty($builder, 'lookupClass'));
        $this->assertEquals([
            '/var/mount/files/',
            null,
        ], $this->getProperty($builder, 'parameters'));

        $builder->uriPrefix("s3://bucket/some/key/prefix/", "renames-[0-9]*\\.gz");

        $this->assertEquals(UriPrefixLookup::class, $this->getProperty($builder, 'lookupClass'));
        $this->assertEquals([
            "s3://bucket/some/key/prefix/",
            "renames-[0-9]*\\.gz",
        ], $this->getProperty($builder, 'parameters'));
    }

    /**
     * @testWith [true]
     *           [false]
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param bool $withDefaults
     */
    public function testCsv(bool $withDefaults): void
    {
        $client  = new DruidClient([]);
        $builder = $client->lookup();

        $parseSpec = Mockery::mock('overload:' . CsvParseSpec::class, ParseSpecInterface::class);
        $parseSpec->shouldReceive('__construct')
            ->once()
            ->with(
                $withDefaults ? ['id', 'first', 'last', 'username', 'address'] : null,
                'id',
                'username',
                !$withDefaults,
                $withDefaults ? 0 : 2
            );

        if ($withDefaults) {
            $result = $builder->csv(
                ['id', 'first', 'last', 'username', 'address'],
                'id',
                'username'
            );
        } else {
            $result = $builder->csv(
                null,
                'id',
                'username',
                true,
                2
            );
        }
        $this->assertEquals($result, $builder);
    }

    /**
     * @testWith [true]
     *           [false]
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     *
     * @param bool $withDefaults
     */
    public function testTsv(bool $withDefaults): void
    {
        $client  = new DruidClient([]);
        $builder = $client->lookup();

        $parseSpec = Mockery::mock('overload:' . TsvParseSpec::class, ParseSpecInterface::class);
        $parseSpec->shouldReceive('__construct')
            ->once()
            ->with(
                $withDefaults ? ['id', 'first', 'last', 'username', 'address'] : null,
                'id',
                'username',
                $withDefaults ? "\t" : ";",
                $withDefaults ? "\x01" : "\n",
                !$withDefaults,
                $withDefaults ? 0 : 5
            );

        if ($withDefaults) {
            $result = $builder->tsv(
                ['id', 'first', 'last', 'username', 'address'],
                'id',
                'username'
            );
        } else {
            $result = $builder->tsv(
                null,
                'id',
                'username',
                ";",
                "\n",
                true,
                5
            );
        }
        $this->assertEquals($result, $builder);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCustomJson(): void
    {
        $client  = new DruidClient([]);
        $builder = $client->lookup();

        $parseSpec = Mockery::mock('overload:' . CustomJsonParseSpec::class, ParseSpecInterface::class);
        $parseSpec->shouldReceive('__construct')
            ->once()
            ->with('id', 'name');

        $result = $builder->customJson('id', 'name');
        $this->assertEquals($result, $builder);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testJson(): void
    {
        $client  = new DruidClient([]);
        $builder = $client->lookup();

        $parseSpec = Mockery::mock('overload:' . SimpleJsonParseSpec::class, ParseSpecInterface::class);
        $parseSpec->shouldReceive('__construct')
            ->once();

        $result = $builder->json();
        $this->assertEquals($result, $builder);
    }
}