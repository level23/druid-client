<?php
declare(strict_types=1);

namespace Level23\Druid\Queries;

use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Concerns\HasLimit;
use Level23\Druid\Types\Granularity;
use Level23\Druid\Concerns\HasFilter;
use Level23\Druid\Concerns\HasHaving;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Concerns\HasIntervals;
use Level23\Druid\Limits\LimitInterface;
use Level23\Druid\Concerns\HasDimensions;
use Level23\Druid\Types\OrderByDirection;
use Level23\Druid\Concerns\HasAggregations;
use Level23\Druid\Context\TopNQueryContext;
use Level23\Druid\Concerns\HasVirtualColumns;
use Level23\Druid\Concerns\HasPostAggregations;
use Level23\Druid\VirtualColumns\VirtualColumn;
use Level23\Druid\Context\GroupByV1QueryContext;
use Level23\Druid\Context\GroupByV2QueryContext;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Context\TimeSeriesQueryContext;
use Level23\Druid\Collections\DimensionCollection;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Collections\VirtualColumnCollection;
use Level23\Druid\Collections\PostAggregationCollection;

class QueryBuilder
{
    use HasFilter, HasHaving, HasDimensions, HasAggregations, HasIntervals, HasLimit, HasPostAggregations, HasVirtualColumns;

    /**
     * @var \Level23\Druid\DruidClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $dataSource;

    /**
     * @var string|\Level23\Druid\Types\Granularity
     */
    protected $granularity;

    /**
     * QueryBuilder constructor.
     *
     * @param \Level23\Druid\DruidClient              $client
     * @param string                                  $dataSource
     * @param string|\Level23\Druid\Types\Granularity $granularity
     */
    public function __construct(DruidClient $client, string $dataSource, $granularity = 'all')
    {
        if (is_string($granularity) && !Granularity::isValid($granularity)) {
            throw new InvalidArgumentException(
                'The given granularity is invalid: ' . $granularity . '. ' .
                'Allowed are: ' . implode(',', Granularity::values())
            );
        }

        $this->client      = $client;
        $this->dataSource  = $dataSource;
        $this->granularity = $granularity;
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
    public function selectVirtual(string $expression, string $as, $outputType = 'string')
    {
        $this->virtualColumns[] = new VirtualColumn($as, $expression, $outputType);

        $this->select($as);

        return $this;
    }

    /**
     * Execute a druid query. We will try to detect the best possible query type possible.
     *
     * @param array $context
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function execute(array $context = []): array
    {
        $query = $this->buildQuery($context);

        $rawResponse = $this->client->executeQuery($query);

        return $query->parseResponse($rawResponse);
    }

    /**
     * Do a segment metadata query and return the response
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function segmentMetadata(): array
    {
        $query = new SegmentMetadataQuery($this->dataSource, new IntervalCollection(...$this->intervals));

        $rawResponse = $this->client->executeQuery($query);

        return $query->parseResponse($rawResponse);
    }

    /**
     * Return the query as a JSON string
     *
     * @param array $context
     *
     * @return string
     * @throws \InvalidArgumentException if the JSON cannot be encoded.
     */
    public function toJson(array $context = []): string
    {
        $query = $this->buildQuery($context);

        $json = \GuzzleHttp\json_encode($query->toArray(), JSON_PRETTY_PRINT);

        return $json;
    }

    /**
     * Return the query as an array
     *
     * @param array $context
     *
     * @return array
     */
    public function toArray(array $context = []): array
    {
        return $this->buildQuery($context)->toArray();
    }

    /**
     * Execute a timeseries query.
     *
     * @param array $context
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function timeseries(array $context = [])
    {
        $query = $this->buildTimeSeriesQuery($context);

        $rawResponse = $this->client->executeQuery($query);

        return $query->parseResponse($rawResponse);
    }

    /**
     * Execute a topN query.
     *
     * @param array $context
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function topN(array $context = [])
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
     * @return array
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function groupBy($context = [])
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
     * @return array
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function groupByV1($context = [])
    {
        $query = $this->buildGroupByQuery($context, 'v1');

        $rawResponse = $this->client->executeQuery($query);

        return $query->parseResponse($rawResponse);
    }

    //<editor-fold desc="Protected methods">

    /**
     * Build a timeseries query.
     *
     * @param array $context
     *
     * @return TimeSeriesQuery
     */
    protected function buildTimeSeriesQuery(array $context = []): TimeSeriesQuery
    {
        $query = new TimeSeriesQuery(
            $this->dataSource,
            new IntervalCollection(...$this->intervals),
            $this->granularity
        );

        // check if we want to use a different output name for the __time column
        if (count($this->dimensions) == 1) {
            $dimension = $this->dimensions[0];
            // did we only retrieve the time dimension?
            if ($dimension->getDimension() == '__time') {
                $query->setTimeOutputName($dimension->getOutputName());
            }
        }

        if (count($context) > 0) {
            $query->setContext(new TimeSeriesQueryContext($context));
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

        if (!$this->limit) {
            return $query;
        }

        $orderByCollection = $this->limit->getOrderByCollection();

        if (count($orderByCollection) != 1) {
            return $query;
        }

        /** @var \Level23\Druid\OrderBy\OrderByInterface $orderBy */
        $orderBy = $orderByCollection[0];

        if ($orderBy->getDimension() == '__time' && $orderBy->getDirection() === OrderByDirection::DESC()) {
            $query->setDescending(true);
        }

        return $query;
    }

    /**
     * Build a topN query.
     *
     * @param array $context
     *
     * @return TopNQuery
     */
    protected function buildTopNQuery(array $context = []): TopNQuery
    {
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

        if (count($this->aggregations) > 0) {
            $query->setAggregations(new AggregationCollection(...$this->aggregations));
        }

        if (count($this->postAggregations) > 0) {
            $query->setPostAggregations(new PostAggregationCollection(...$this->postAggregations));
        }

        if (count($context) > 0) {
            $query->setContext(new TopNQueryContext($context));
        }

        if ($this->filter) {
            $query->setFilter($this->filter);
        }

        return $query;
    }

    /**
     * Build the group by query
     *
     * @param array|GroupByV2QueryContext|GroupByV1QueryContext $context
     * @param string                                            $type
     *
     * @return GroupByQuery
     */
    protected function buildGroupByQuery($context = [], string $type = 'v2'): GroupByQuery
    {
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

        // @todo : subtotalsSpec

        if ($this->having) {
            $query->setHaving($this->having);
        }

        return $query;
    }

    /**
     * Return the query automatically detected based on the requested data.
     *
     * @param array $context
     *
     * @return \Level23\Druid\Queries\QueryInterface
     */
    protected function buildQuery(array $context = []): QueryInterface
    {
        /**
         * If we only have "grouped" by __time, then we can use a time series query.
         * This is preferred, because it's a lot faster then doing a group by query.
         */
        if ($this->isTimeSeriesQuery()) {
            return $this->buildTimeSeriesQuery($context);
        }

        // Check if we can use a topN query.
        if ($this->isTopNQuery()) {
            return $this->buildTopNQuery($context);
        }

        return $this->buildGroupByQuery($context);
    }

    /**
     * Determine if the current query is a timeseries query
     *
     * @return bool
     */
    protected function isTimeSeriesQuery()
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
    protected function isTopNQuery()
    {
        if (count($this->dimensions) != 1) {
            return false;
        }

        return $this->limit
            && $this->limit->getLimit() != self::$DEFAULT_MAX_LIMIT
            && count($this->limit->getOrderByCollection()) == 1;
    }
    //</editor-fold>
}
