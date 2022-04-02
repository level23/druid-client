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
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Collections\VirtualColumnCollection;
use Level23\Druid\HavingFilters\HavingFilterInterface;
use Level23\Druid\Collections\PostAggregationCollection;

class GroupByQuery implements QueryInterface
{
    protected string $dataSource;

    protected DimensionCollection $dimensions;

    protected string $granularity;

    protected ?FilterInterface $filter;

    protected ?AggregationCollection $aggregations;

    protected ?PostAggregationCollection $postAggregations;

    protected ?VirtualColumnCollection $virtualColumns;

    protected ?HavingFilterInterface $having;

    protected ?ContextInterface $context;

    protected ?Limit $limit = null;

    protected IntervalCollection $intervals;

    protected array $subtotals = [];

    /**
     * GroupByQuery constructor.
     *
     * @param string                                         $dataSource
     * @param \Level23\Druid\Collections\DimensionCollection $dimensions
     * @param \Level23\Druid\Collections\IntervalCollection  $intervals
     * @param null|array|AggregationCollection               $aggregations
     * @param string                                         $granularity
     */
    public function __construct(
        string $dataSource,
        DimensionCollection $dimensions,
        IntervalCollection $intervals,
        $aggregations = null,
        string $granularity = 'all'
    ) {
        $this->dataSource  = $dataSource;
        $this->dimensions  = $dimensions;
        $this->granularity = Granularity::validate($granularity);
        $this->intervals   = $intervals;

        if ($aggregations) {
            $this->setAggregations($aggregations);
        }
    }

    /**
     * Return the query in array format, so we can fire it to druid.
     *
     * @return array
     */
    public function toArray(): array
    {
        $query = [
            'queryType'   => 'groupBy',
            'dataSource'  => $this->dataSource,
            'intervals'   => $this->intervals->toArray(),
            'dimensions'  => $this->dimensions->toArray(),
            'granularity' => $this->granularity,
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

        if ($this->context) {
            $query['context'] = $this->context->toArray();
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
    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @param \Level23\Druid\Collections\AggregationCollection|array $aggregations
     */
    public function setAggregations($aggregations)
    {
        if (is_array($aggregations)) {
            $aggregations = new AggregationCollection(...$aggregations);
        }

        $this->aggregations = $aggregations;
    }

    /**
     * @param \Level23\Druid\Collections\PostAggregationCollection|array $postAggregations
     */
    public function setPostAggregations($postAggregations): void
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
    public function setLimit($limit): void
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
     * @param array $response
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
     * @param array $subtotals
     */
    public function setSubtotals(array $subtotals): void
    {
        $this->subtotals = $subtotals;
    }
}