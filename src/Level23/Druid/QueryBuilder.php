<?php
declare(strict_types=1);

namespace Level23\Druid;

use ArrayObject;
use Carbon\Carbon;
use Closure;
use InvalidArgumentException;
use Level23\Druid\Aggregations\CountAggregator;
use Level23\Druid\Aggregations\DistinctCountAggregator;
use Level23\Druid\Aggregations\FirstAggregator;
use Level23\Druid\Aggregations\LastAggregator;
use Level23\Druid\Aggregations\MaxAggregator;
use Level23\Druid\Aggregations\MinAggregator;
use Level23\Druid\Aggregations\SumAggregator;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Collections\DimensionCollection;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Collections\PostAggregationCollection;
use Level23\Druid\Context\GroupByQueryContext;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Dimensions\DimensionInterface;
use Level23\Druid\Dimensions\LookupDimension;
use Level23\Druid\Filters\AndFilter;
use Level23\Druid\Filters\BoundFilter;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Filters\InFilter;
use Level23\Druid\Filters\JavascriptFilter;
use Level23\Druid\Filters\LikeFilter;
use Level23\Druid\Filters\LogicalExpressionFilterInterface;
use Level23\Druid\Filters\LogicalExpressionHavingFilterInterface;
use Level23\Druid\Filters\NotFilter;
use Level23\Druid\Filters\OrFilter;
use Level23\Druid\Filters\RegexFilter;
use Level23\Druid\Filters\SearchFilter;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\HavingFilters\AndHavingFilter;
use Level23\Druid\HavingFilters\DimensionSelectorHavingFilter;
use Level23\Druid\HavingFilters\EqualToHavingFilter;
use Level23\Druid\HavingFilters\GreaterThanHavingFilter;
use Level23\Druid\HavingFilters\HavingFilterInterface;
use Level23\Druid\HavingFilters\LessThanHavingFilter;
use Level23\Druid\HavingFilters\NotHavingFilter;
use Level23\Druid\HavingFilters\OrHavingFilter;
use Level23\Druid\HavingFilters\QueryHavingFilter;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Limits\Limit;
use Level23\Druid\Limits\LimitInterface;
use Level23\Druid\OrderBy\OrderBy;
use Level23\Druid\Queries\GroupByQuery;
use Level23\Druid\Queries\QueryInterface;
use Level23\Druid\Queries\TimeSeriesQuery;
use Level23\Druid\Queries\TopNQuery;
use Level23\Druid\Types\DataType;
use Level23\Druid\Types\Granularity;
use Level23\Druid\Types\OrderByDirection;
use Level23\Druid\Types\SortingOrder;

class QueryBuilder
{
    /**
     * @var \Level23\Druid\DruidClient
     */
    protected $client;

    /**
     * @var \Level23\Druid\Filters\FilterInterface|null
     */
    protected $filter;

    /**
     * @var string
     */
    protected $dataSource;

    /**
     * @var \Level23\Druid\Collections\DimensionCollection
     */
    protected $dimensions;

    /**
     * @var \Level23\Druid\Collections\IntervalCollection
     */
    protected $intervals;

    /**
     * @var \Level23\Druid\Collections\AggregationCollection
     */
    protected $aggregations;

    /**
     * @var \Level23\Druid\Collections\PostAggregationCollection
     */
    protected $postAggregations;

    /**
     * @var string|\Level23\Druid\Types\Granularity
     */
    protected $granularity;

    /**
     * @var \Level23\Druid\Limits\LimitInterface|null
     */
    protected $limit;

    /**
     * @var HavingFilterInterface|null
     */
    protected $having;

    public const DEFAULT_MAX_LIMIT = 999999;

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

        $this->client           = $client;
        $this->dataSource       = $dataSource;
        $this->dimensions       = new DimensionCollection();
        $this->intervals        = new IntervalCollection();
        $this->aggregations     = new AggregationCollection();
        $this->postAggregations = new PostAggregationCollection();
        $this->granularity      = $granularity;
    }

    /**
     * Filter records where the given dimension exists in the given list of items
     *
     * @param string $dimension
     * @param array  $items
     *
     * @return \Level23\Druid\QueryBuilder
     */
    public function whereIn(string $dimension, array $items): QueryBuilder
    {
        $filter = new InFilter($dimension, $items);

        $this->where($filter);

        return $this;
    }

    /**
     * Filter records where the given dimension NOT exists in the given list of items
     *
     * @param string $dimension
     * @param array  $items
     *
     * @return \Level23\Druid\QueryBuilder
     */
    public function whereNotIn(string $dimension, array $items): QueryBuilder
    {
        $filter = new NotFilter(new InFilter($dimension, $items));

        $this->where($filter);

        return $this;
    }

    /**
     * Add an interval, eg the date where we want to select data from.
     * This can be an Carbon or DateTime object, or a string which can be parsed to a datetime.
     *
     * @param \Carbon\Carbon|string $start
     * @param \Carbon\Carbon|string $stop
     *
     * @return $this
     */
    public function interval($start, $stop): QueryBuilder
    {
        if (!$start instanceof Carbon) {
            $start = Carbon::parse($start);
        }

        if (!$stop instanceof Carbon) {
            $stop = Carbon::parse($stop);
        }

        $this->intervals->add(new Interval($start, $stop));

        return $this;
    }

    /**
     * Select a dimension from our statistics. Possible use a lookup function to find the
     * real data which we want to use.
     *
     * @param array|\ArrayObject|string|DimensionInterface $dimensions
     * @param string                                       $as                      When dimensions is a string (the
     *                                                                              dimension), you can specify the
     *                                                                              alias output name here.
     *
     * @param string                                       $lookupFunction          Optional, the lookup function which
     *                                                                              you want to use
     * @param bool                                         $retainMissingValue      Do you want to retain values which
     *                                                                              cannot be found in the registered
     *                                                                              lookup function?
     * @param string                                       $replaceMissingValueWith If you want to retain missing
     *                                                                              values, you can specify here what
     *                                                                              you want to use.
     *
     * @return $this
     */
    public function select(
        $dimensions,
        string $as = '',
        string $lookupFunction = '',
        bool $retainMissingValue = false,
        string $replaceMissingValueWith = ''
    ): QueryBuilder {
        if ($dimensions instanceof DimensionInterface) {
            $this->dimensions->add($dimensions);
        } elseif (is_string($dimensions) && !empty($lookupFunction)) {
            $this->dimensions->add(new LookupDimension(
                $dimensions,
                $lookupFunction,
                ($as ?: $dimensions),
                $retainMissingValue,
                $replaceMissingValueWith
            ));
        } elseif (is_string($dimensions) && empty($lookupFunction)) {
            $this->dimensions->add(new Dimension($dimensions, ($as ?: $dimensions)));
        } elseif ($dimensions instanceof ArrayObject) {
            $this->dimensions->addFromArray($dimensions->getArrayCopy());
        } elseif (is_array($dimensions)) {
            $this->dimensions->addFromArray($dimensions);
        }

        return $this;
    }

    /**
     * Sum the given metric
     *
     * @param string          $metric
     * @param string          $as
     * @param string|DataType $type
     *
     * @return \Level23\Druid\QueryBuilder
     */
    public function sum(string $metric, string $as = '', $type = 'long'): QueryBuilder
    {
        $this->aggregations->add(new SumAggregator($metric, $as, $type));

        return $this;
    }

    /**
     * Count the number of results and put it in a dimension with the given name.
     *
     * @param string $as
     *
     * @return \Level23\Druid\QueryBuilder
     */
    public function count(string $as): QueryBuilder
    {
        $this->aggregations->add(new CountAggregator($as));

        return $this;
    }

    /**
     * Count the number of distinct values of a specific dimension.
     * NOTE: The DataSketches Theta Sketch extension is required to run this aggregation.
     *
     * @param string $dimension
     * @param string $as
     * @param int    $size Must be a power of 2. Internally, size refers to the maximum number of entries sketch object
     *                     will retain. Higher size means higher accuracy but more space to store sketches. Note that
     *                     after you index with a particular size, druid will persist sketch in segments and you will
     *                     use size greater or equal to that at query time. See the DataSketches site for details. In
     *                     general, We recommend just sticking to default size.
     *
     * @return \Level23\Druid\QueryBuilder
     */
    public function distinctCount(string $dimension, string $as = '', $size = 16384): QueryBuilder
    {
        $this->aggregations->add(new DistinctCountAggregator($dimension, ($as ?: $dimension), $size));

        return $this;
    }

    /**
     * Get the minimum value for the given metric
     *
     * @param string $metric
     * @param string $as
     * @param string $type
     *
     * @return \Level23\Druid\QueryBuilder
     */
    public function min(string $metric, string $as = '', $type = 'long'): QueryBuilder
    {
        $this->aggregations->add(new MinAggregator($metric, $as, $type));

        return $this;
    }

    /**
     * Get the maximum value for the given metric
     *
     * @param string $metric
     * @param string $as
     * @param string $type
     *
     * @return \Level23\Druid\QueryBuilder
     */
    public function max(string $metric, string $as = '', $type = 'long'): QueryBuilder
    {
        $this->aggregations->add(new MaxAggregator($metric, $as, $type));

        return $this;
    }

    /**
     * Get the first metric found
     *
     * @param string $metric
     * @param string $as
     * @param string $type
     *
     * @return \Level23\Druid\QueryBuilder
     */
    public function first(string $metric, string $as = '', $type = 'long'): QueryBuilder
    {
        $this->aggregations->add(new FirstAggregator($metric, $as, $type));

        return $this;
    }

    /**
     * Get the last metric found
     *
     * @param string $metric
     * @param string $as
     * @param string $type
     *
     * @return \Level23\Druid\QueryBuilder
     */
    public function last(string $metric, string $as = '', $type = 'long'): QueryBuilder
    {
        $this->aggregations->add(new LastAggregator($metric, $as, $type));

        return $this;
    }

    /**
     * Filter our results where the given dimension matches the value based on the operator.
     * The operator can be '=', '>', '>=', '<', '<=', '<>', '!=' or 'like', 'regex', 'javascript', 'in'
     *
     * @param string|\Level23\Druid\Filters\FilterInterface|\Closure $filterOrDimensionOrClosure
     * @param string|null                                            $operator
     * @param mixed                                                  $value
     * @param string                                                 $boolean
     *
     * @return \Level23\Druid\QueryBuilder
     */
    public function where(
        $filterOrDimensionOrClosure,
        $operator = null,
        $value = null,
        $boolean = 'and'
    ): QueryBuilder {
        $filter = null;
        if (is_string($filterOrDimensionOrClosure)) {
            if ($value === null && !empty($operator)) {
                $value    = $operator;
                $operator = '=';
            }

            $operator = strtolower((string)$operator);

            if ($operator == '=') {
                $filter = new SelectorFilter($filterOrDimensionOrClosure, $value);
            } elseif ($operator == '<>' || $operator == '!=') {
                $filter = new NotFilter(new SelectorFilter($filterOrDimensionOrClosure, $value));
            } elseif (in_array($operator, ['>', '>=', '<', '<='])) {
                $filter = new BoundFilter($filterOrDimensionOrClosure, $operator, (string)$value);
            } elseif ($operator == 'like') {
                $filter = new LikeFilter($filterOrDimensionOrClosure, $value);
            } elseif ($operator == 'javascript') {
                $filter = new JavascriptFilter($filterOrDimensionOrClosure, $value);
            } elseif ($operator == 'regex' || $operator == 'regexp') {
                $filter = new RegexFilter($filterOrDimensionOrClosure, $value);
            } elseif ($operator == 'search') {
                $filter = new SearchFilter($filterOrDimensionOrClosure, $value);
            } elseif ($operator == 'in') {
                $filter = new InFilter($filterOrDimensionOrClosure, $value);
            }
        } elseif ($filterOrDimensionOrClosure instanceof FilterInterface) {
            $filter = $filterOrDimensionOrClosure;
        } elseif ($filterOrDimensionOrClosure instanceof Closure) {

            // lets create a bew builder object where the user can mess around with
            $obj = new QueryBuilder($this->client, $this->dataSource, $this->granularity);

            // call the user function
            call_user_func($filterOrDimensionOrClosure, $obj);

            // Now retrieve the filter which was created and add it to our current filter set.
            $filter = $obj->getFilter();
        }

        if ($filter === null) {
            return $this;
        }

        if ($this->filter === null) {
            $this->filter = $filter;

            return $this;
        }

        $this->addFilter(
            $filter,
            $boolean == 'and' ? AndFilter::class : OrFilter::class
        );

        return $this;
    }

    /**
     * @param string|FilterInterface $filterOrDimension
     * @param string|null            $operator
     * @param mixed|null             $value
     *
     * @return \Level23\Druid\QueryBuilder
     */
    public function orWhere($filterOrDimension, $operator = null, $value = null): QueryBuilder
    {
        return $this->where($filterOrDimension, $operator, $value, 'or');
    }

    /**
     * Execute a druid query. We will try to detect the best possible query type possible.
     *
     * @param array $context
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\DruidException
     * @throws \Level23\Druid\Exceptions\DruidQueryException
     */
    public function execute(array $context = []): array
    {
        $query = $this->buildQueryAutomatic($context);

        return $this->client->executeDruidQuery($query);
    }

    /**
     * Return the query as a JSON string
     *
     * @param array $context
     *
     * @return string
     */
    public function toJson(array $context = []): string
    {
        $query = $this->buildQueryAutomatic($context);

        $json = json_encode($query->getQuery(), JSON_PRETTY_PRINT);
        if ($json === false) {
            return "";
        }

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
        $query = $this->buildQueryAutomatic($context);

        return $query->getQuery();
    }

    /**
     * Execute a timeseries query.
     *
     * @param array $context
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\DruidException
     * @throws \Level23\Druid\Exceptions\DruidQueryException
     */
    public function timeseries(array $context = [])
    {
        $query = $this->buildTimeSeriesQuery($context);

        return $this->client->executeDruidQuery($query);
    }

    /**
     * Execute a topN query.
     *
     * @param array $context
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\DruidException
     * @throws \Level23\Druid\Exceptions\DruidQueryException
     */
    public function topN(array $context = [])
    {
        $query = $this->buildTopNQuery($context);

        return $this->client->executeDruidQuery($query);
    }

    /**
     * Return the group by query
     *
     * @param array $context
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\DruidException
     * @throws \Level23\Druid\Exceptions\DruidQueryException
     */
    public function groupBy(array $context = [])
    {
        $query = $this->buildGroupByQuery($context);

        return $this->client->executeDruidQuery($query);
    }

    /**
     * Limit out result by N records.
     *
     * @param int $limit
     *
     * @return $this
     */
    public function limit(int $limit): QueryBuilder
    {
        if ($this->limit instanceof LimitInterface) {
            $this->limit->setLimit($limit);
        } else {
            $this->limit = new Limit($limit);
        }

        return $this;
    }

    /**
     * Build our "having" part of the query.
     *
     * The operator can be '=', '>', '>=', '<', '<=', '<>', '!=' or 'like'
     *
     * @param string|HavingFilterInterface|Closure $havingOrMetricOrClosure
     * @param string|null                          $operator
     * @param string|null                          $value
     * @param string                               $boolean
     *
     * @return \Level23\Druid\QueryBuilder
     */
    public function having(
        $havingOrMetricOrClosure,
        $operator = null,
        $value = null,
        $boolean = 'and'
    ): QueryBuilder {
        $having = null;

        if ($value === null && !empty($operator)) {
            $value    = $operator;
            $operator = '=';
        }

        if (is_string($havingOrMetricOrClosure) && is_string($operator) && $value !== null) {
            if ($operator == '=') {
                $value = (string)$value;
                /**
                 * Check if this is a aggregator metric.
                 */
                $isAggregator = false;

                foreach ($this->aggregations as $aggregation) {
                    /** @var \Level23\Druid\Aggregations\AggregatorInterface $aggregation */
                    if ($aggregation->getOutputName() == $havingOrMetricOrClosure) {
                        $isAggregator = true;
                        break;
                    }
                }

                if ($isAggregator) {
                    $having = new EqualToHavingFilter($havingOrMetricOrClosure, floatval($value));
                } else {
                    $having = new DimensionSelectorHavingFilter($havingOrMetricOrClosure, $value);
                }
            } elseif ($operator == '<>' || $operator == '!=') {
                $having = new NotHavingFilter(new EqualToHavingFilter($havingOrMetricOrClosure, floatval($value)));
            } elseif ($operator == '>') {
                $having = new GreaterThanHavingFilter($havingOrMetricOrClosure, floatval($value));
            } elseif ($operator == '<') {
                $having = new LessThanHavingFilter($havingOrMetricOrClosure, floatval($value));
            } elseif ($operator == '>=') {
                $having = new OrHavingFilter(
                    new GreaterThanHavingFilter($havingOrMetricOrClosure, floatval($value)),
                    new EqualToHavingFilter($havingOrMetricOrClosure, floatval($value))
                );
            } elseif ($operator == '<=') {
                $having = new OrHavingFilter(
                    new LessThanHavingFilter($havingOrMetricOrClosure, floatval($value)),
                    new EqualToHavingFilter($havingOrMetricOrClosure, floatval($value))
                );
            } elseif (strtolower($operator) == 'like') {
                $having = new QueryHavingFilter(new LikeFilter($havingOrMetricOrClosure, $value));
            }
        } elseif ($havingOrMetricOrClosure instanceof FilterInterface) {
            $having = new QueryHavingFilter($havingOrMetricOrClosure);
        } elseif ($havingOrMetricOrClosure instanceof HavingFilterInterface) {
            $having = $havingOrMetricOrClosure;
        } elseif ($havingOrMetricOrClosure instanceof Closure) {

            // lets create a bew builder object where the user can mess around with
            $obj = new QueryBuilder($this->client, $this->dataSource, $this->granularity);

            // call the user function
            call_user_func($havingOrMetricOrClosure, $obj);

            // Now retrieve the having filter which was created and add it to our current filter set.
            /**
             * @var HavingFilterInterface $filter
             */
            $having = $obj->getHaving();
        }

        if ($having === null) {
            return $this;
        }

        if ($this->having === null) {
            $this->having = $having;

            return $this;
        }

        $this->addHaving(
            $having,
            $boolean == 'and' ? AndHavingFilter::class : OrHavingFilter::class
        );

        return $this;
    }

    /**
     * @param string                  $dimension
     * @param string|OrderByDirection $direction
     * @param string|SortingOrder     $dimensionOrder
     *
     * @return \Level23\Druid\QueryBuilder
     */
    public function orderBy(string $dimension, $direction, $dimensionOrder = 'lexicographic'): QueryBuilder
    {
        if (is_string($direction)) {
            $direction = strtolower($direction);
            if ($direction == "asc") {
                $direction = OrderByDirection::ASC();
            } elseif ($direction == "desc") {
                $direction = OrderByDirection::DESC();
            }
        }

        $order = new OrderBy($dimension, $direction, $dimensionOrder);

        if (!$this->limit) {
            $this->limit = new Limit(self::DEFAULT_MAX_LIMIT);
        }

        $this->limit->addOrderBy($order);

        return $this;
    }

    //<editor-fold desc="Getters">

    /**
     * @return \Level23\Druid\Filters\FilterInterface|null
     */
    public function getFilter(): ?FilterInterface
    {
        return $this->filter;
    }

    /**
     * @return \Level23\Druid\HavingFilters\HavingFilterInterface|null
     */
    public function getHaving(): ?HavingFilterInterface
    {
        return $this->having;
    }

    /**
     * @return \Level23\Druid\Collections\AggregationCollection
     */
    public function getAggregations(): AggregationCollection
    {
        return $this->aggregations;
    }

    /**
     * @return \Level23\Druid\Collections\DimensionCollection
     */
    public function getDimensions(): DimensionCollection
    {
        return $this->dimensions;
    }

    //</editor-fold>

    //<editor-fold desc="Protected methods">

    /**
     * Helper method to add a filter
     *
     * @param FilterInterface $filter
     * @param string          $type
     *
     * @return \Level23\Druid\QueryBuilder
     */
    protected function addFilter(FilterInterface $filter, string $type)
    {
        if ($this->filter instanceof LogicalExpressionFilterInterface && $this->filter instanceof $type) {
            $this->filter->addFilter($filter);
        } else {
            $filters = [$this->filter, $filter];

            $this->filter = new $type($filters);
        }

        return $this;
    }

    /**
     * Helper method to add a filter
     *
     * @param \Level23\Druid\HavingFilters\HavingFilterInterface $havingFilter
     * @param string                                             $type
     *
     * @return \Level23\Druid\QueryBuilder
     */
    protected function addHaving(HavingFilterInterface $havingFilter, string $type)
    {
        if ($this->having instanceof LogicalExpressionHavingFilterInterface && $this->having instanceof $type) {
            $this->having->addHavingFilter($havingFilter);
        } else {
            $filters = [$this->having, $havingFilter];

            $this->having = new $type($filters);
        }

        return $this;
    }

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
            $this->intervals,
            $this->granularity
        );

        if (count($context) > 0) {
            $query->setContext(new GroupByQueryContext($context));
        }

        if ($this->filter) {
            $query->setFilter($this->filter);
        }

        if (count($this->aggregations) > 0) {
            $query->setAggregations($this->aggregations);
        }

        if (count($this->postAggregations) > 0) {
            $query->setPostAggregations($this->postAggregations);
        }

        if ($this->limit) {
            $orderByCollection = $this->limit->getOrderByCollection();
            if (count($orderByCollection) == 1) {
                /** @var \Level23\Druid\OrderBy\OrderByInterface $orderBy */
                $orderBy = $orderByCollection[0];
                if ($orderBy->getDimension() == '__time' && $orderBy->getDirection() === OrderByDirection::DESC()) {
                    $query->setDescending(true);
                }
            }
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
    public function buildTopNQuery(array $context = []): TopNQuery
    {
        if (!$this->limit instanceof LimitInterface) {
            throw new InvalidArgumentException('You should specify a limit to make use of a top query');
        }

        $orderByCollection = $this->limit->getOrderByCollection();
        $orderBy           = $orderByCollection[0];

        /** @var \Level23\Druid\OrderBy\OrderByInterface $orderBy */
        $query = new TopNQuery(
            $this->dataSource,
            $this->intervals,
            $this->dimensions[0],
            $this->limit->getLimit(),
            $orderBy->getDimension(),
            $this->granularity
        );

        if (count($this->aggregations) > 0) {
            $query->setAggregations($this->aggregations);
        }

        if (count($this->postAggregations) > 0) {
            $query->setPostAggregations($this->postAggregations);
        }

        if (count($context) > 0) {
            $query->setContext(new GroupByQueryContext($context));
        }

        if ($this->filter) {
            $query->setFilter($this->filter);
        }

        return $query;
    }

    /**
     * Build the group by query
     *
     * @param array $context
     *
     * @return GroupByQuery
     */
    public function buildGroupByQuery(array $context = []): GroupByQuery
    {
        $query = new GroupByQuery(
            $this->dataSource,
            $this->dimensions,
            $this->intervals,
            $this->aggregations,
            $this->granularity
        );

        if (count($context) > 0) {
            $query->setContext(new GroupByQueryContext($context));
        }

        if (count($this->postAggregations) > 0) {
            $query->setPostAggregations($this->postAggregations);
        }

        if ($this->filter) {
            $query->setFilter($this->filter);
        }

        if ($this->limit) {
            $query->setLimit($this->limit);
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
    protected function buildQueryAutomatic(array $context = []): QueryInterface
    {
        $type = 'groupBy';

        /**
         * If we only have "grouped" by __time, then we can use a time series query.
         * This is preferred, because it's a lot faster then doing a group by query.
         */
        if (count($this->dimensions) == 1) {
            /** @var DimensionInterface $dimension */
            $config = $this->dimensions[0]->getDimension();

            // did we only retrieve the time dimension?
            if ($config['dimension'] == '__time') {
                $type = 'timeseries';
            } // Check if we can use a topN query.
            elseif (
                $this->limit
                && $this->limit->getLimit() != self::DEFAULT_MAX_LIMIT
                && count($this->limit->getOrderByCollection()) == 1
            ) {
                // We can use a topN!
                $type = 'topN';
            }
        }

        switch ($type) {
            case 'timeseries':
                $query = $this->buildTimeSeriesQuery($context);
                break;

            case 'topN':
                $query = $this->buildTopNQuery($context);
                break;

            default:
            case 'groupBy':
                $query = $this->buildGroupByQuery($context);
                break;
        }

        return $query;
    }
    //</editor-fold>
}

