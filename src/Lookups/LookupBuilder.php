<?php
declare(strict_types=1);

namespace Level23\Druid\Lookups;

use DateTime;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Lookups\ParseSpecs\CsvParseSpec;
use Level23\Druid\Lookups\ParseSpecs\TsvParseSpec;
use Level23\Druid\Lookups\ParseSpecs\ParseSpecInterface;
use Level23\Druid\Lookups\ParseSpecs\CustomJsonParseSpec;
use Level23\Druid\Lookups\ParseSpecs\SimpleJsonParseSpec;

/**
 * This class provides functionality to fetch lookups, create/update and delete them.
 */
class LookupBuilder
{
    protected bool $injective = false;

    protected int $firstCacheTimeoutMs = 0;

    protected int|string|null $pollPeriod = null;

    protected int|null $maxHeapPercentage = null;

    /**
     * @var class-string<\Level23\Druid\Lookups\LookupInterface>|null
     */
    protected ?string $lookupClass = null;

    /**
     * @var array <int,mixed>
     */
    protected array $parameters = [];

    protected ?ParseSpecInterface $parseSpec = null;

    public function __construct(protected DruidClient $druidClient)
    {

    }

    /**
     * This will create or update a lookup.
     * Assign a unique version identifier each time you update a lookup extractor factory. Otherwise, the call will
     * fail. If no version was specified, we will automatically use the current date and time as version number.
     *
     * @see https://druid.apache.org/docs/latest/api-reference/lookups-api
     * @see https://druid.apache.org/docs/latest/querying/lookups-cached-global
     * @see https://druid.apache.org/docs/latest/querying/kafka-extraction-namespace
     *
     * @param string      $lookupName
     * @param string      $tier
     * @param string|null $versionName
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function store(
        string $lookupName,
        string $tier = '__default',
        string $versionName = null
    ): void {
        if ($this->lookupClass === null) {
            throw new InvalidArgumentException('No lookup defined. Please define the lookup by using the map, kafka, jdbc, uri or uriPrefix methods!');
        }

        if ($this->lookupClass === UriLookup::class || $this->lookupClass === UriPrefixLookup::class) {

            if ($this->parseSpec === null) {
                throw new InvalidArgumentException('Using an URI lookup, but there is no parseSpec defined! Use the csv, tsv, simpleJson or customJson methods to define the parseSpec.');
            }

            $parameters   = $this->parameters;
            $parameters[] = $this->pollPeriod;
            $parameters[] = $this->maxHeapPercentage;
            $parameters[] = $this->injective;
            $parameters[] = $this->firstCacheTimeoutMs;
            $lookup       = new $this->lookupClass(
                $this->parseSpec,
                ...$parameters
            );
        } elseif ($this->lookupClass === JdbcLookup::class) {
            $parameters   = $this->parameters;
            $parameters[] = $this->pollPeriod;
            $parameters[] = $this->maxHeapPercentage;
            $parameters[] = $this->injective;
            $parameters[] = $this->firstCacheTimeoutMs;

            $lookup = new $this->lookupClass(...$parameters);
        } elseif ($this->lookupClass === KafkaLookup::class) {
            $parameters   = $this->parameters;
            $parameters[] = $this->injective;

            $lookup = new $this->lookupClass(...$parameters);
        } else {
            $lookup = new $this->lookupClass(
                ...$this->parameters
            );
        }

        $payload = [
            'version'                => $versionName ?? (new DateTime())->format('Y-m-d\TH:i:s.000\Z'),
            'lookupExtractorFactory' => $lookup->toArray(),
        ];

        $this->druidClient->executeRawRequest(
            'post',
            $this->druidClient->config('coordinator_url') . '/druid/coordinator/v1/lookups/config/' . $tier . '/' . $lookupName,
            $payload,
        );
    }

    /**
     * Return all keys for the given lookup.
     *
     * @param string $lookupName
     *
     * @return array<int,int|string|float>
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function keys(string $lookupName): array
    {
        /**
         * Druid facilitates an endpoint for the keys:
         * <broker>/druid/v1/lookups/introspect/<lookup>/keys
         *
         * Unfortunately the response is not valid json. Therefore, we cannot use it.
         *
         * @see https://github.com/apache/druid/issues/17361
         */
        $all = $this->introspect($lookupName);

        return array_keys($all);
    }

    /**
     * Return all values for the given lookup.
     *
     * @param string $lookupName
     *
     * @return array<int,int|string|float|bool|null>
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function values(string $lookupName): array
    {
        /**
         * Druid facilitates an endpoint for the values:
         * <broker>/druid/v1/lookups/introspect/<lookup>/values
         *
         * Unfortunately the response is not valid json. Therefore, we cannot use it.
         *
         * @see https://github.com/apache/druid/issues/17361
         */
        $all = $this->introspect($lookupName);

        return array_values($all);
    }

    /**
     * Return the content of the lookup
     *
     * @param string $lookupName
     *
     * @return array<int|string|float,int|string|float|bool|null>
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function introspect(string $lookupName): array
    {
        /** @var array<int|string,int|string> $response */
        $response = $this->druidClient->executeRawRequest(
            'get',
            $this->druidClient->config('broker_url') . '/druid/v1/lookups/introspect/' . $lookupName,
        );

        return $response;
    }

    /**
     * Delete the given lookup in the given tier. When this fails an exception is thrown.
     *
     * @param string $lookupName
     * @param string $tier
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function delete(string $lookupName, string $tier = '__default'): void
    {
        $this->druidClient->executeRawRequest(
            'delete',
            $this->druidClient->config('coordinator_url') . '/druid/coordinator/v1/lookups/config/' . $tier . '/' . $lookupName,
        );
    }

    /**
     * Return all tiers and all of their lookups in one large configuration array.
     *
     *
     * @return array<string,mixed>
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function all(): array
    {
        /** @var array<string,mixed> $response */
        $response = $this->druidClient->executeRawRequest(
            'get',
            $this->druidClient->config('coordinator_url') . '/druid/coordinator/v1/lookups/config/all',
        );

        return $response;
    }

    /**
     * Return a list of known tier names in the dynamic configuration.
     *
     * @return array<int,string>
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function tiers(bool $discover = true): array
    {
        /** @var array<int,string> $response */
        $response = $this->druidClient->executeRawRequest(
            'get',
            $this->druidClient->config('coordinator_url') . '/druid/coordinator/v1/lookups/config?discover=' . ($discover ? 'true' : 'false'),
        );

        return $response;
    }

    /**
     * For Uri and JDBC lookups it is possible to define a first cache timeout.
     * With this method you can set it.
     *
     * @param int $ms
     *
     * @return $this
     */
    public function firstCacheTimeout(int $ms = 0): self
    {
        $this->firstCacheTimeoutMs = $ms;

        return $this;
    }

    /**
     * For Uri and JDBC lookups it is possible to define if the content is injective or not.
     * Injective means that each key item points to a unique value. So each key and value is unique.
     * If so, druid kan make internal optimizations.
     *
     * @param bool $injective
     *
     * @return $this
     */
    public function injective(bool $injective = true): self
    {
        $this->injective = $injective;

        return $this;
    }

    /**
     * Set the polling period for the lookup to configure. This is applied for JDBC, URI and URIPrefix lookups.
     *
     * @param string|int $period Period between polling for updates. For example PT10M for every 10 minutes, or use
     *                           milliseconds like 600000. When not given, the data is fetched only once.
     *
     * @return $this
     */
    public function pollPeriod(int|string $period): self
    {
        $this->pollPeriod = $period;

        return $this;
    }

    /**
     * Set the max heap percentage for the lookup to configure. This is applied for JDBC, URI and URIPrefix lookups.
     *
     * @param int $maxHeapPercentage The maximum percentage of heap size that the lookup should consume. If the lookup
     *                               grows beyond this size, warning messages will be logged in the respective service
     *                               logs.
     *
     * @return $this
     */
    public function maxHeapPercentage(int $maxHeapPercentage): self
    {
        $this->maxHeapPercentage = $maxHeapPercentage;

        return $this;
    }

    /**
     * Return all lookup names defined under the given tier.
     *
     * @param string $tier
     *
     * @return array<int,string>
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function names(string $tier = '__default'): array
    {
        /** @var array<int,string> $response */
        $response = $this->druidClient->executeRawRequest(
            'get',
            $this->druidClient->config('coordinator_url') . '/druid/coordinator/v1/lookups/config/' . $tier,
        );

        return $response;
    }

    /**
     * Return the lookup as it is currently configured in Druid.
     *
     * @param string $name
     * @param string $tier
     *
     * @return array<string,mixed>
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function get(string $name, string $tier = '__default'): array
    {
        /** @var array<string,mixed> $response */
        $response = $this->druidClient->executeRawRequest(
            'get',
            $this->druidClient->config('coordinator_url') . '/druid/coordinator/v1/lookups/config/' . $tier . '/' . $name,
        );

        return $response;
    }

    /**
     * Configure a map lookup.
     *
     * @param array<int|float|string,int|string|float|bool|null> $map
     *
     * @return \Level23\Druid\Lookups\LookupBuilder
     */
    public function map(array $map): self
    {
        $this->lookupClass = MapLookup::class;
        $this->parameters  = [$map];

        return $this;
    }

    /**
     * Configure a kafka lookup.
     *
     * @see https://druid.apache.org/docs/latest/querying/kafka-extraction-namespace
     *
     * @param string                   $kafkaTopic      The Kafka topic to read the data from
     * @param string|array<int,string> $servers         The kafka server(s), for example ["kafka1.service:9092",
     *                                                  "kafka2.service:9092"]
     * @param array<string,string>     $kafkaProperties Other optional kafka properties.
     * @param int                      $connectTimeout  How long to wait for an initial connection
     *
     * @return $this
     */
    public function kafka(
        string $kafkaTopic,
        string|array $servers,
        array $kafkaProperties = [],
        int $connectTimeout = 0
    ): LookupBuilder {
        $this->lookupClass = KafkaLookup::class;
        $this->parameters  = [$kafkaTopic, $servers, $kafkaProperties, $connectTimeout];

        return $this;
    }

    /**
     * Configure a new JDBC lookup.
     *
     * @see https://druid.apache.org/docs/latest/querying/lookups-cached-global/#jdbc-lookup
     *
     * @param string      $connectUri             The URI where to connect to. For example
     *                                            "jdbc:mysql://localhost:3306/druid"
     * @param string|null $username               The username for the connection, or null when not used.
     * @param string|null $password               The password for the connection, or null when not used.
     * @param string      $table                  The table where to retrieve the data from.
     * @param string      $keyColumn              The column from the table which is used as key for the lookup.
     * @param string      $valueColumn            The column from the table which is used as value from the lookup.
     * @param string|null $filter                 Specify a filter (like a where statement) which should be used in the
     *                                            query to fetch the data from the database.
     * @param string|null $tsColumn               Specify a column which contains a datetime. Druid will use this to
     *                                            only fetch rows from the database which have been changed since the
     *                                            last poll request. This reduces database load and is highly
     *                                            recommended!
     * @param int|null    $jitterSeconds          How much jitter to add (in seconds) up to maximum as a delay (actual
     *                                            value will be used as random from 0 to jitterSeconds), used to
     *                                            distribute db load more evenly.
     * @param int|null    $loadTimeoutSeconds     How much time (in seconds) it can take to query and populate lookup
     *                                            values. It will be helpful in lookup updates. On lookup update, it
     *                                            will wait maximum of loadTimeoutSeconds for new lookup to come up and
     *                                            continue serving from old lookup until new lookup successfully loads.
     *
     * @return $this
     */
    public function jdbc(
        string $connectUri,
        string|null $username,
        string|null $password,
        string $table,
        string $keyColumn,
        string $valueColumn,
        ?string $filter = null,
        ?string $tsColumn = null,
        ?int $jitterSeconds = null,
        ?int $loadTimeoutSeconds = null
    ): self {
        $this->lookupClass = JdbcLookup::class;
        $this->parameters  = [
            $connectUri,
            $username,
            $password,
            $table,
            $keyColumn,
            $valueColumn,
            $filter,
            $tsColumn,
            $jitterSeconds,
            $loadTimeoutSeconds,
        ];

        return $this;
    }

    /**
     * Configure a new URI lookup. Do not forget to specify the file specification by calling the `csv`, `tsv`, `json`
     * or `customJson` methods.
     *
     * @param string $uri URI for the lookup file. Can be a file, HDFS, S3 or GCS path.
     *
     * @return $this
     */
    public function uri(string $uri): self
    {
        $this->lookupClass = UriLookup::class;
        $this->parameters  = [$uri];

        return $this;
    }

    /**
     * Configure a new URI lookup for files matching a given pattern.
     *
     * Do not forget to specify the file specification by calling the `csv`, `tsv`, `json`
     * or `customJson` methods.
     *
     * @param string      $uriPrefix A URI prefix that specifies a directory or other searchable resource where lookup
     *                               files are located
     * @param string|null $fileRegex Regex for matching the file name under uriPrefix, for example "*.json"
     *
     * @return $this
     */
    public function uriPrefix(string $uriPrefix, ?string $fileRegex = null): self
    {
        $this->lookupClass = UriPrefixLookup::class;
        $this->parameters  = [$uriPrefix, $fileRegex];

        return $this;
    }

    /**
     * Specify that the file which is being parsed by a URI or URIPrefix lookup is a CSV file.
     * If both skipHeaderRows and hasHeaderRow options are set, skipHeaderRows is first applied. For example, if you
     * set skipHeaderRows to 2 and hasHeaderRow to true, Druid will skip the first two lines and then extract column
     * information from the third line.
     *
     * @param array<int,string>|null $columns        The list of columns in the csv file, or use null and set
     *                                               $hasHeaderRow to true to fetch it automatically.
     * @param string|null            $keyColumn      The name of the column containing the key
     * @param string|null            $valueColumn    The name of the column containing the value
     * @param bool                   $hasHeaderRow   Set to true to indicate that column information can be extracted
     *                                               from the input files' header row
     * @param int                    $skipHeaderRows Number of header rows to be skipped
     *
     * @return $this
     */
    public function csv(
        ?array $columns = null,
        ?string $keyColumn = null,
        ?string $valueColumn = null,
        bool $hasHeaderRow = false,
        int $skipHeaderRows = 0
    ): LookupBuilder {

        $this->parseSpec = new CsvParseSpec($columns, $keyColumn, $valueColumn, $hasHeaderRow, $skipHeaderRows);

        return $this;
    }

    /**
     * Specify that the file which is being parsed by a URI or URIPrefix lookup is a JSON file.
     *
     * @param string $keyFieldName   The field name of the key
     * @param string $valueFieldName The field name of the value
     *
     * @return $this
     */
    public function customJson(
        string $keyFieldName,
        string $valueFieldName
    ): LookupBuilder {
        $this->parseSpec = new CustomJsonParseSpec($keyFieldName, $valueFieldName);

        return $this;
    }

    /**
     * Specify that the file which is being parsed by a URI or URIPrefix lookup is a JSON file containing key => value
     * items. For example:
     *
     * ```
     * {"foo": "bar"}
     * {"baz": "bat"}
     * {"buck": "truck"}
     * ```
     *
     * @return $this
     */
    public function json(): LookupBuilder
    {
        $this->parseSpec = new SimpleJsonParseSpec();

        return $this;
    }

    /**
     * Specify that the file which is being parsed by a URI or URIPrefix lookup is a TSV file.
     *
     * If both skipHeaderRows and hasHeaderRow options are set, skipHeaderRows is first applied. For example, if you
     * set skipHeaderRows to 2 and hasHeaderRow to true, Druid will skip the first two lines and then extract column
     * information from the third line.
     *
     * @param array<int,string>|null $columns        The list of columns in the TSV file, or use null and set
     *                                               $hasHeaderRow to true to fetch it automatically.
     * @param string|null            $keyColumn      The name of the column containing the key
     * @param string|null            $valueColumn    The name of the column containing the value
     * @param string                 $delimiter      The delimiter in the file
     * @param string                 $listDelimiter  The list delimiter in the file
     * @param bool                   $hasHeaderRow   Set to true to indicate that column information can be extracted
     *                                               from the input files' header row
     * @param int                    $skipHeaderRows Number of header rows to be skipped
     *
     * @return $this
     */
    public function tsv(
        ?array $columns,
        ?string $keyColumn = null,
        ?string $valueColumn = null,
        string $delimiter = "\t",
        string $listDelimiter = "\x01",
        bool $hasHeaderRow = false,
        int $skipHeaderRows = 0
    ): LookupBuilder {
        $this->parseSpec = new TsvParseSpec(
            $columns,
            $keyColumn,
            $valueColumn,
            $delimiter,
            $listDelimiter,
            $hasHeaderRow,
            $skipHeaderRows
        );

        return $this;
    }
}