<?php
declare(strict_types=1);

namespace Level23\Druid\Queries;

use Level23\Druid\Limits\Limit;
use Level23\Druid\Types\Granularity;
use Level23\Druid\Limits\LimitInterface;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Context\ContextInterface;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Responses\GroupByQueryResponse;
use Level23\Druid\Collections\DimensionCollection;
use Level23\Druid\DataSources\DataSourceInterface;
use Level23\Druid\Aggregations\AggregatorInterface;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Collections\VirtualColumnCollection;
use Level23\Druid\HavingFilters\HavingFilterInterface;
use Level23\Druid\Collections\PostAggregationCollection;
use Level23\Druid\PostAggregations\PostAggregatorInterface;

class GroupByQuery implements QueryInterface
{
    protected DataSourceInterface $dataSource;

    protected DimensionCollection $dimensions;

    protected Granularity $granularity;

    protected ?FilterInterface $filter = null;

    protected ?AggregationCollection $aggregations = null;

    protected ?PostAggregationCollection $postAggregations = null;

    protected ?VirtualColumnCollection $virtualColumns = null;

    protected ?HavingFilterInterface $having = null;

    protected ?ContextInterface $context = null;

    protected ?Limit $limit = null;

    protected IntervalCollection $intervals;

    /**
     * @var array<array<string>>
     */
    protected array $subtotals = [];

    /**
     * GroupByQuery constructor.
     *
     * @param DataSourceInterface                                   $dataSource
     * @param \Level23\Druid\Collections\DimensionCollection        $dimensions
     * @param \Level23\Druid\Collections\IntervalCollection         $intervals
     * @param AggregationCollection|array<AggregatorInterface>|null $aggregations
     * @param string|Granularity                                    $granularity
     */
    public function __construct(
        DataSourceInterface $dataSource,
        DimensionCollection $dimensions,
        IntervalCollection $intervals,
        array|AggregationCollection|null $aggregations = null,
        string|Granularity $granularity = 'all'
    ) {
        $this->dataSource  = $dataSource;
        $this->dimensions  = $dimensions;
        $this->granularity = is_string($granularity) ? Granularity::from(strtolower($granularity)) : $granularity;
        $this->intervals   = $intervals;

        if ($aggregations) {
            $this->setAggregations($aggregations);
        }
    }

    /**
     * Return the query in array format, so we can fire it to druid.
     *
     * @return array<string,string|array<mixed>>
     */
    public function toArray(): array
    {
        $query = [
            'queryType'   => 'groupBy',
            'dataSource'  => $this->dataSource->toArray(),
            'intervals'   => $this->intervals->toArray(),
            'dimensions'  => $this->dimensions->toArray(),
            'granularity' => $this->granularity->value,
        ];

        if ($this->filter) {
            $query['filter'] = $this->filter->toArray();
        }

        if ($this->aggregations) {
            $query['aggregations'] = $this->aggregations->toArray();
        }

        if ($this->postAggregations) {
            $query['postAggregations'] = $this->postAggregations->toArray();
        }

        if ($this->virtualColumns) {
            $query['virtualColumns'] = $this->virtualColumns->toArray();
        }

        if ($this->having) {
            $query['having'] = $this->having->toArray();
        }

        if (isset($this->context)) {
            $context = $this->context->toArray();
            if (sizeof($context) > 0) {
                $query['context'] = $context;
            }
        }

        if ($this->limit) {
            $query['limitSpec'] = $this->limit->toArray();
        }

        if (count($this->subtotals) > 0) {
            $query['subtotalsSpec'] = $this->subtotals;
        }

        return $query;
    }

    /**
     * @param \Level23\Druid\Filters\FilterInterface $filter
     */
    public function setFilter(FilterInterface $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * @param \Level23\Druid\Collections\AggregationCollection|array<AggregatorInterface> $aggregations
     */
    public function setAggregations(array|AggregationCollection $aggregations): void
    {
        if (is_array($aggregations)) {
            $aggregations = new AggregationCollection(...$aggregations);
        }

        $this->aggregations = $aggregations;
    }

    /**
     * @param \Level23\Druid\Collections\PostAggregationCollection|array<PostAggregatorInterface> $postAggregations
     */
    public function setPostAggregations(PostAggregationCollection|array $postAggregations): void
    {
        if (is_array($postAggregations)) {
            $postAggregations = new PostAggregationCollection(...$postAggregations);
        }

        $this->postAggregations = $postAggregations;
    }

    /**
     * @param \Level23\Druid\HavingFilters\HavingFilterInterface $having
     */
    public function setHaving(HavingFilterInterface $having): void
    {
        $this->having = $having;
    }

    /**
     * @param \Level23\Druid\Context\ContextInterface $context
     */
    public function setContext(ContextInterface $context): void
    {
        $this->context = $context;
    }

    /**
     * @param \Level23\Druid\Limits\Limit|int $limit
     */
    public function setLimit(Limit|int $limit): void
    {
        if ($limit instanceof LimitInterface) {
            $this->limit = $limit;
        } else {

            if (!$this->limit) {
                $this->limit = new Limit();
            }

            $this->limit->setLimit($limit);
        }
    }

    /**
     * The "offset" parameter tells Druid to skip this many rows when returning results.
     *
     * @param int $offset
     */
    public function setOffset(int $offset): void
    {
        if (!$this->limit) {
            $this->limit = new Limit();
        }
        $this->limit->setOffset($offset);
    }

    /**
     * Parse the response into something we can return to the user.
     *
     * @param array<string|int,string|int|array<mixed>> $response
     *
     * @return GroupByQueryResponse
     */
    public function parseResponse(array $response): GroupByQueryResponse
    {
        return new GroupByQueryResponse($response);
    }

    /**
     * @param \Level23\Druid\Collections\VirtualColumnCollection $virtualColumns
     */
    public function setVirtualColumns(VirtualColumnCollection $virtualColumns): void
    {
        $this->virtualColumns = $virtualColumns;
    }

    /**
     * @param array<array<string>> $subtotals
     */
    public function setSubtotals(array $subtotals): void
    {
        $this->subtotals = $subtotals;
    }
}