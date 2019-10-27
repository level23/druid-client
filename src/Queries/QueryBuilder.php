<?php
declare(strict_types=1);

namespace Level23\Druid\Queries;

use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Types\DataType;
use Level23\Druid\Concerns\HasLimit;
use Level23\Druid\Types\Granularity;
use Level23\Druid\Concerns\HasFilter;
use Level23\Druid\Concerns\HasHaving;
use Level23\Druid\Types\SortingOrder;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Context\QueryContext;
use Level23\Druid\Concerns\HasIntervals;
use Level23\Druid\Limits\LimitInterface;
use Level23\Druid\Concerns\HasDimensions;
use Level23\Druid\Types\OrderByDirection;
use Level23\Druid\Responses\QueryResponse;
use Level23\Druid\Concerns\HasAggregations;
use Level23\Druid\Context\TopNQueryContext;
use Level23\Druid\Context\ScanQueryContext;
use Level23\Druid\Concerns\HasSearchFilters;
use Level23\Druid\Concerns\HasVirtualColumns;
use Level23\Druid\Types\ScanQueryResultFormat;
use Level23\Druid\Responses\ScanQueryResponse;
use Level23\Druid\Responses\TopNQueryResponse;
use Level23\Druid\VirtualColumns\VirtualColumn;
use Level23\Druid\Concerns\HasPostAggregations;
use Level23\Druid\Context\GroupByV1QueryContext;
use Level23\Druid\Context\GroupByV2QueryContext;
use Level23\Druid\Responses\SelectQueryResponse;
use Level23\Druid\Responses\SearchQueryResponse;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Context\TimeSeriesQueryContext;
use Level23\Druid\Responses\GroupByQueryResponse;
use Level23\Druid\Collections\DimensionCollection;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Responses\TimeSeriesQueryResponse;
use Level23\Druid\Collections\VirtualColumnCollection;
use Level23\Druid\Collections\PostAggregationCollection;
use Level23\Druid\Responses\SegmentMetadataQueryResponse;

class QueryBuilder
{
    use HasFilter, HasHaving, HasDimensions, HasAggregations, HasIntervals, HasLimit, HasVirtualColumns, HasPostAggregations, HasSearchFilters;

    /**
     * @var \Level23\Druid\DruidClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $dataSource;

    /**
     * @var string
     */
    protected $granularity;

    /**
     * @var array|\Level23\Druid\PostAggregations\PostAggregatorInterface[]
     */
    protected $postAggregations = [];

    /**
     * Set a paging identifier for a Select query.
     *
     * @var array|null
     */
    protected $pagingIdentifier;

    /**
     * The subtotal spec (only applies for groupBy queries)
     *
     * @var array
     */
    protected $subtotals = [];

    /**
     * The metrics to select when using a Select Query.
     * When empty, all metrics are returned.
     *
     * @var array
     */
    protected $metrics = [];

    /**
     * QueryBuilder constructor.
     *
     * @param \Level23\Druid\DruidClient $client
     * @param string                     $dataSource
     * @param string                     $granularity
     */
    public function __construct(DruidClient $client, string $dataSource, string $granularity = Granularity::ALL)
    {
        $this->client      = $client;
        $this->dataSource  = $dataSource;
        $this->granularity = Granularity::validate($granularity);
    }

    /**
     * Create a virtual column and select the result.
     *
     * Virtual columns are queryable column "views" created from a set of columns during a query.
     *
     * A virtual column can potentially draw from multiple underlying columns, although a virtual column always
     * presents itself as a single column.
     *
     * @param string $expression
     * @param string $as
     * @param string $outputType
     *
     * @return $this
     * @see https://druid.apache.org/docs/latest/misc/math-expr.html
     */
    public function selectVirtual(string $expression, string $as, $outputType = DataType::STRING)
    {
        $this->virtualColumns[] = new VirtualColumn($expression, $as, $outputType);

        $this->select($as);

        return $this;
    }

    /**
     * Execute a druid query. We will try to detect the best possible query type possible.
     *
     * @param array|QueryContext $context
     *
     * @return QueryResponse
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function execute($context = []): QueryResponse
    {
        $query = $this->buildQuery($context);

        $rawResponse = $this->client->executeQuery($query);

        return $query->parseResponse($rawResponse);
    }

    /**
     * Update/set the dataSource
     *
     * @param string $dataSource
     *
     * @return $this
     */
    public function dataSource(string $dataSource): QueryBuilder
    {
        $this->dataSource = $dataSource;

        return $this;
    }

    /**
     * Update/set the granularity
     *
     * @param string $granularity
     *
     * @return $this
     */
    public function granularity(string $granularity): QueryBuilder
    {
        $this->granularity = Granularity::validate($granularity);

        return $this;
    }

    /**
     * Define an array which should contain arrays with dimensions where you want to retrieve the subtotals for.
     * NOTE: This only applies for groupBy queries.
     *
     * This is like doing a WITH ROLLUP in an SQL query.
     *
     * Example: Imagine that you count the number of people. You want to do this for per city, but also
     * per province, per country and per continent. This method allows you to do that all at once. Druid will
     * do the "sum(people)" per subtotal row.
     *
     * Example:
     * Array(
     *   Array('continent', 'country', 'province', 'city'),
     *   Array('continent', 'country', 'province'),
     *   Array('continent', 'country'),
     *   Array('continent'),
     * )
     *
     * @param array $subtotals
     *
     * @return $this
     */
    public function subtotals(array $subtotals)
    {
        $this->subtotals = $subtotals;

        return $this;
    }

    /**
     * Select the metrics which should be returned when using a selectQuery.
     * If this is not specified, all metrics are returned (which is default).
     *
     * NOTE: This only applies to select queries!
     *
     * @param array $metrics
     *
     * @return $this
     */
    public function metrics(array $metrics)
    {
        $this->metrics = $metrics;

        return $this;
    }

    /**
     * Set a paging identifier. This is only applied for a SELECT query!
     *
     * @param array $pagingIdentifier
     *
     * @return \Level23\Druid\Queries\QueryBuilder
     */
    public function pagingIdentifier(array $pagingIdentifier): QueryBuilder
    {
        $this->pagingIdentifier = $pagingIdentifier;

        return $this;
    }

    /**
     * Do a segment metadata query and return the response
     *
     * @return SegmentMetadataQueryResponse
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function segmentMetadata(): SegmentMetadataQueryResponse
    {
        $query = new SegmentMetadataQuery($this->dataSource, new IntervalCollection(...$this->intervals));

        $rawResponse = $this->client->executeQuery($query);

        return $query->parseResponse($rawResponse);
    }

    /**
     * Return the query as a JSON string
     *
     * @param array|QueryContext $context
     *
     * @return string
     * @throws \InvalidArgumentException if the JSON cannot be encoded.
     */
    public function toJson($context = []): string
    {
        $query = $this->buildQuery($context);

        $json = \GuzzleHttp\json_encode($query->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return $json;
    }

    /**
     * Return the query as an array
     *
     * @param array|QueryContext $context
     *
     * @return array
     */
    public function toArray($context = []): array
    {
        return $this->buildQuery($context)->toArray();
    }

    /**
     * Execute a TimeSeries query.
     *
     * @param array|TimeSeriesQueryContext $context
     *
     * @return TimeSeriesQueryResponse
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function timeseries($context = []): TimeSeriesQueryResponse
    {
        $query = $this->buildTimeSeriesQuery($context);

        $rawResponse = $this->client->executeQuery($query);

        return $query->parseResponse($rawResponse);
    }

    /**
     * Execute a Scan Query.
     *
     * @param array|ScanQueryContext $context      Query context parameters
     * @param int|null               $rowBatchSize How many rows buffered before return to client. Default is 20480
     * @param bool                   $legacy       Return results consistent with the legacy "scan-query" contrib
     *                                             extension. Defaults to the value set by druid.query.scan.legacy,
     *                                             which in turn defaults to false. See Legacy mode for details.
     * @param string                 $resultFormat Result Format. Use one of the ScanQueryResultFormat::* constants.
     *
     * @return ScanQueryResponse
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function scan(
        $context = [],
        int $rowBatchSize = null,
        bool $legacy = false,
        string $resultFormat = ScanQueryResultFormat::NORMAL_LIST
    ): ScanQueryResponse {
        $query = $this->buildScanQuery($context, $rowBatchSize, $legacy, $resultFormat);

        $rawResponse = $this->client->executeQuery($query);

        return $query->parseResponse($rawResponse);
    }

    /**
     * Execute a select query.
     *
     * @param array|QueryContext $context
     *
     * @return SelectQueryResponse
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function selectQuery($context = []): SelectQueryResponse
    {
        $query = $this->buildSelectQuery($context);

        $rawResponse = $this->client->executeQuery($query);

        return $query->parseResponse($rawResponse);
    }

    /**
     * Execute a topN query.
     *
     * @param array|TopNQueryContext $context
     *
     * @return TopNQueryResponse
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function topN($context = []): TopNQueryResponse
    {
        $query = $this->buildTopNQuery($context);

        $rawResponse = $this->client->executeQuery($query);

        return $query->parseResponse($rawResponse);
    }

    /**
     * Return the group by query
     *
     * @param array|GroupByV2QueryContext|GroupByV1QueryContext $context
     *
     * @return GroupByQueryResponse
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function groupBy($context = []): GroupByQueryResponse
    {
        $query = $this->buildGroupByQuery($context, 'v2');

        $rawResponse = $this->client->executeQuery($query);

        return $query->parseResponse($rawResponse);
    }

    /**
     * Return the group by query
     *
     * @param array|GroupByV2QueryContext|GroupByV1QueryContext $context
     *
     * @return GroupByQueryResponse
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function groupByV1($context = []): GroupByQueryResponse
    {
        $query = $this->buildGroupByQuery($context, 'v1');

        $rawResponse = $this->client->executeQuery($query);

        return $query->parseResponse($rawResponse);
    }

    /**
     * Execute a search query and return the response
     *
     * @param array|QueryContext $context
     * @param string             $sortingOrder
     *
     * @return \Level23\Druid\Responses\SearchQueryResponse
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function search($context = [], string $sortingOrder = SortingOrder::LEXICOGRAPHIC): SearchQueryResponse
    {
        $query = $this->buildSearchQuery($context, $sortingOrder);

        $rawResponse = $this->client->executeQuery($query);

        return $query->parseResponse($rawResponse);
    }

    //<editor-fold desc="Protected methods">

    /**
     * In our previous version we required an `order()` with "__time" for TimeSeries, Scan and Select Queries.
     * This method makes sure that we are backwards compatible.
     *
     * @param string|null $dimension If given, we will also check if this dimension was ordered by.
     *
     * @return bool|null
     */
    protected function legacyIsOrderByDirectionDescending(string $dimension = null): ?bool
    {
        if ($this->limit) {
            $orderBy = $this->limit->getOrderByCollection();

            if ($orderBy->count() > 0) {
                $orderByItems = $orderBy->toArray();
                $first        = reset($orderByItems);

                if ($first['dimension'] == '__time' || ($dimension && $dimension == $first['dimension'])) {
                    return $first['direction'] == OrderByDirection::DESC;
                }
            }
        }

        return null;
    }

    /**
     * Build a search query.
     *
     * @param array  $context
     * @param string $sortingOrder
     *
     * @return \Level23\Druid\Queries\SearchQuery
     */
    protected function buildSearchQuery($context = [], string $sortingOrder = SortingOrder::LEXICOGRAPHIC): SearchQuery
    {
        if (count($this->intervals) == 0) {
            throw new InvalidArgumentException('You have to specify at least one interval');
        }

        if (!$this->searchFilter) {
            throw new InvalidArgumentException('You have to specify a search filter!');
        }

        $query = new SearchQuery(
            $this->dataSource,
            $this->granularity,
            new IntervalCollection(...$this->intervals),
            $this->searchFilter
        );

        if (count($this->searchDimensions) > 0) {
            $query->setDimensions($this->searchDimensions);
        }

        if (is_array($context) && count($context) > 0) {
            $query->setContext(new QueryContext($context));
        } elseif ($context instanceof QueryContext) {
            $query->setContext($context);
        }

        if ($this->filter) {
            $query->setFilter($this->filter);
        }

        if ($sortingOrder) {
            $query->setSort($sortingOrder);
        }

        if ($this->limit) {
            $query->setLimit($this->limit->getLimit());
        }

        return $query;
    }

    /**
     * Build a select query.
     *
     * @param array|QueryContext $context
     *
     * @return \Level23\Druid\Queries\SelectQuery
     */
    protected function buildSelectQuery($context = []): SelectQuery
    {
        if (count($this->intervals) == 0) {
            throw new InvalidArgumentException('You have to specify at least one interval');
        }

        if (!$this->limit || $this->limit->getLimit() == self::$DEFAULT_MAX_LIMIT) {
            throw new InvalidArgumentException('You have to supply a limit');
        }

        $limit = $this->limit->getLimit();

        $descending = false;
        if ($this->direction) {
            $descending = ($this->direction === OrderByDirection::DESC);
        } elseif ($this->legacyIsOrderByDirectionDescending() === true) {
            $descending = true;
        }

        $query = new SelectQuery(
            $this->dataSource,
            new IntervalCollection(...$this->intervals),
            $limit,
            count($this->dimensions) > 0 ? new DimensionCollection(...$this->dimensions) : null,
            $this->metrics,
            $descending
        );

        if ($this->pagingIdentifier) {
            $query->setPagingIdentifier($this->pagingIdentifier);
        }

        if (is_array($context) && count($context) > 0) {
            $query->setContext(new QueryContext($context));
        } elseif ($context instanceof QueryContext) {
            $query->setContext($context);
        }

        return $query;
    }

    /**
     * Build a scan query.
     *
     * @param array|QueryContext $context
     * @param int|null           $rowBatchSize
     * @param bool               $legacy
     * @param string             $resultFormat
     *
     * @return \Level23\Druid\Queries\ScanQuery
     */
    protected function buildScanQuery(
        $context = [],
        int $rowBatchSize = null,
        bool $legacy = false,
        string $resultFormat = ScanQueryResultFormat::NORMAL_LIST
    ) {
        if (count($this->intervals) == 0) {
            throw new InvalidArgumentException('You have to specify at least one interval');
        }

        if (!$this->isDimensionsListScanCompliant()) {
            throw new InvalidArgumentException(
                'Only simple dimension or metric selects are available in a scan query. ' .
                'Aliases, extractions or lookups are not available.'
            );
        }

        $query = new ScanQuery(
            $this->dataSource,
            new IntervalCollection(...$this->intervals)
        );

        $columns = [];
        foreach ($this->dimensions as $dimension) {
            $columns[] = $dimension->getDimension();
        }

        if ($this->direction) {
            $query->setOrder($this->direction);
        } else {
            $isDescending = $this->legacyIsOrderByDirectionDescending();
            if ($isDescending !== null) {
                $query->setOrder($isDescending ? OrderByDirection::DESC : OrderByDirection::ASC);
            }
        }

        if (count($columns) > 0) {
            $query->setColumns($columns);
        }

        if ($this->filter) {
            $query->setFilter($this->filter);
        }

        if ($this->limit && $this->limit->getLimit() && $this->limit->getLimit() != self::$DEFAULT_MAX_LIMIT) {
            $query->setLimit($this->limit->getLimit());
        }

        if (is_array($context) && count($context) > 0) {
            $query->setContext(new ScanQueryContext($context));
        } elseif ($context instanceof QueryContext) {
            $query->setContext($context);
        }

        if ($resultFormat) {
            $query->setResultFormat($resultFormat);
        }

        if ($rowBatchSize) {
            $query->setBatchSize($rowBatchSize);
        }

        $query->setLegacy($legacy);

        return $query;
    }

    /**
     * Build a TimeSeries query.
     *
     * @param array|QueryContext $context
     *
     * @return TimeSeriesQuery
     */
    protected function buildTimeSeriesQuery($context = []): TimeSeriesQuery
    {
        if (count($this->intervals) == 0) {
            throw new InvalidArgumentException('You have to specify at least one interval');
        }

        $query = new TimeSeriesQuery(
            $this->dataSource,
            new IntervalCollection(...$this->intervals),
            $this->granularity
        );

        // check if we want to use a different output name for the __time column
        $dimension = null;
        if (count($this->dimensions) == 1) {
            $dimension = $this->dimensions[0];
            // did we only retrieve the time dimension?
            if ($dimension->getDimension() == '__time' && $dimension->getOutputName() != '__time') {
                $query->setTimeOutputName($dimension->getOutputName());
            }
        }

        if (is_array($context) && count($context) > 0) {
            $query->setContext(new TimeSeriesQueryContext($context));
        } elseif ($context instanceof QueryContext) {
            $query->setContext($context);
        }

        if ($this->filter) {
            $query->setFilter($this->filter);
        }

        if (count($this->aggregations) > 0) {
            $query->setAggregations(new AggregationCollection(...$this->aggregations));
        }

        if (count($this->postAggregations) > 0) {
            $query->setPostAggregations(new PostAggregationCollection(...$this->postAggregations));
        }

        if (count($this->virtualColumns) > 0) {
            $query->setVirtualColumns(new VirtualColumnCollection(...$this->virtualColumns));
        }

        // If there is a limit set, then apply this on the time series query.
        if ($this->limit && $this->limit->getLimit() != self::$DEFAULT_MAX_LIMIT) {
            $query->setLimit($this->limit->getLimit());
        }

        $descending = false;
        if ($this->direction) {
            $descending = ($this->direction === OrderByDirection::DESC);
        } elseif ($this->legacyIsOrderByDirectionDescending($dimension ? $dimension->getOutputName() : null) === true) {
            $descending = true;
        }

        if ($descending) {
            $query->setDescending($descending);
        }

        return $query;
    }

    /**
     * Build a topN query.
     *
     * @param array|QueryContext $context
     *
     * @return TopNQuery
     */
    protected function buildTopNQuery($context = []): TopNQuery
    {
        if (count($this->intervals) == 0) {
            throw new InvalidArgumentException('You have to specify at least one interval');
        }

        if (!$this->limit instanceof LimitInterface) {
            throw new InvalidArgumentException(
                'You should specify a limit to make use of a top query'
            );
        }

        $orderByCollection = $this->limit->getOrderByCollection();
        if (count($orderByCollection) == 0) {
            throw new InvalidArgumentException(
                'You should specify a an order by direction to make use of a top query'
            );
        }

        /**
         * @var \Level23\Druid\OrderBy\OrderBy $orderBy
         */
        $orderBy = $orderByCollection[0];

        $metric = $orderBy->getDimension();

        /** @var \Level23\Druid\OrderBy\OrderByInterface $orderBy */
        $query = new TopNQuery(
            $this->dataSource,
            new IntervalCollection(...$this->intervals),
            $this->dimensions[0],
            $this->limit->getLimit(),
            $metric,
            $this->granularity
        );

        $query->setDescending(
            ($orderBy->getDirection() == OrderByDirection::DESC)
        );

        if (count($this->aggregations) > 0) {
            $query->setAggregations(new AggregationCollection(...$this->aggregations));
        }

        if (count($this->postAggregations) > 0) {
            $query->setPostAggregations(new PostAggregationCollection(...$this->postAggregations));
        }

        if (count($this->virtualColumns) > 0) {
            $query->setVirtualColumns(new VirtualColumnCollection(...$this->virtualColumns));
        }

        if (is_array($context) && count($context) > 0) {
            $query->setContext(new TopNQueryContext($context));
        } elseif ($context instanceof QueryContext) {
            $query->setContext($context);
        }

        if ($this->filter) {
            $query->setFilter($this->filter);
        }

        return $query;
    }

    /**
     * Build the group by query
     *
     * @param array|QueryContext $context
     * @param string             $type
     *
     * @return GroupByQuery
     */
    protected function buildGroupByQuery($context = [], string $type = 'v2'): GroupByQuery
    {
        if (count($this->intervals) == 0) {
            throw new InvalidArgumentException('You have to specify at least one interval');
        }

        $query = new GroupByQuery(
            $this->dataSource,
            new DimensionCollection(...$this->dimensions),
            new IntervalCollection(...$this->intervals),
            new AggregationCollection(...$this->aggregations),
            $this->granularity
        );

        if (is_array($context)) {
            switch ($type) {
                case 'v1':
                    $context = new GroupByV1QueryContext($context);
                    break;

                default:
                case 'v2':
                    $context = new GroupByV2QueryContext($context);
                    break;
            }
        }

        $query->setContext($context);

        if (count($this->postAggregations) > 0) {
            $query->setPostAggregations(new PostAggregationCollection(...$this->postAggregations));
        }

        if ($this->filter) {
            $query->setFilter($this->filter);
        }

        if ($this->limit) {
            $query->setLimit($this->limit);
        }

        if (count($this->virtualColumns) > 0) {
            $query->setVirtualColumns(new VirtualColumnCollection(...$this->virtualColumns));
        }

        if (count($this->subtotals) > 0) {
            $query->setSubtotals($this->subtotals);
        }

        if ($this->having) {
            $query->setHaving($this->having);
        }

        return $query;
    }

    /**
     * Return the query automatically detected based on the requested data.
     *
     * @param array|QueryContext $context
     *
     * @return \Level23\Druid\Queries\QueryInterface
     */
    protected function buildQuery($context = []): QueryInterface
    {
        // Check if this is a scan query. This is the preferred way to query when there are
        // no aggregations done.
        if ($this->isScanQuery()) {
            return $this->buildScanQuery($context);
        }

        // If we only have "grouped" by __time, then we can use a time series query.
        // This is preferred, because it's a lot faster then doing a group by query.
        if ($this->isTimeSeriesQuery()) {
            return $this->buildTimeSeriesQuery($context);
        }

        // Check if we can use a topN query.
        if ($this->isTopNQuery()) {
            return $this->buildTopNQuery($context);
        }

        // Check if we can use a select query.
        if ($this->isSelectQuery()) {
            return $this->buildSelectQuery($context);
        }

        // Check if we can use a search query.
        if ($this->isSearchQuery()) {
            return $this->buildSearchQuery($context);
        }

        return $this->buildGroupByQuery($context, 'v2');
    }

    /**
     * Determine if the current query is a TimeSeries query
     *
     * @return bool
     */
    protected function isTimeSeriesQuery(): bool
    {
        if (count($this->dimensions) != 1) {
            return false;
        }

        return $this->dimensions[0]->getDimension() == '__time'
            && $this->dimensions[0] instanceof Dimension
            && $this->dimensions[0]->getExtractionFunction() === null;
    }

    /**
     * Determine if the current query is topN query
     *
     * @return bool
     */
    protected function isTopNQuery(): bool
    {
        if (count($this->dimensions) != 1) {
            return false;
        }

        return $this->limit
            && $this->limit->getLimit() != self::$DEFAULT_MAX_LIMIT
            && count($this->limit->getOrderByCollection()) == 1;
    }

    /**
     * Check if we should use a select query.
     *
     * @return bool
     */
    protected function isSelectQuery(): bool
    {
        return $this->pagingIdentifier !== null && count($this->aggregations) == 0;
    }

    /**
     * Check if we should use a search query.
     *
     * @return bool
     */
    protected function isSearchQuery(): bool
    {
        return !empty($this->searchFilter);
    }

    /**
     * Check if we should use a scan query.
     *
     * @return bool
     */
    protected function isScanQuery(): bool
    {
        return count($this->aggregations) == 0 && $this->isDimensionsListScanCompliant();
    }

    /**
     * Return true if the dimensions which are selected can be used as "columns" in a scan query.
     *
     * @return bool
     */
    protected function isDimensionsListScanCompliant(): bool
    {
        foreach ($this->dimensions as $dimension) {
            if (!$dimension instanceof Dimension) {
                return false;
            }

            if ($dimension->getExtractionFunction()) {
                return false;
            }

            if ($dimension->getDimension() != $dimension->getOutputName()) {
                return false;
            }
        }

        return true;
    }
    //</editor-fold>
}

