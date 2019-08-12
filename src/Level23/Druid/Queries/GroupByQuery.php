<?php
declare(strict_types=1);

namespace Level23\Druid\Queries;

use InvalidArgumentException;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Collections\DimensionCollection;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Collections\PostAggregationCollection;
use Level23\Druid\Context\ContextInterface;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\HavingFilters\HavingFilterInterface;
use Level23\Druid\Limits\Limit;
use Level23\Druid\Limits\LimitInterface;
use Level23\Druid\Types\Granularity;

class GroupByQuery implements QueryInterface
{
    /**
     * @var string
     */
    protected $dataSource;

    /**
     * @var \Level23\Druid\Collections\DimensionCollection
     */
    protected $dimensions;

    /**
     * @var \Level23\Druid\Types\Granularity|string
     */
    protected $granularity;

    /**
     * @var \Level23\Druid\Filters\FilterInterface|null
     */
    protected $filter;

    /**
     * @var \Level23\Druid\Collections\AggregationCollection|null
     */
    protected $aggregations;

    /**
     * @var \Level23\Druid\Collections\PostAggregationCollection|null
     */
    protected $postAggregations;

    /**
     * @var \Level23\Druid\Collections\VirtualColumnCollection|null
     */
    protected $virtualColumns;

    /**
     * @var \Level23\Druid\HavingFilters\HavingFilterInterface|null
     */
    protected $having;

    /**
     * @var \Level23\Druid\Context\ContextInterface|null
     */
    protected $context;

    /**
     * @var \Level23\Druid\Limits\LimitInterface|null
     */
    protected $limit;

    /**
     * @var \Level23\Druid\Collections\IntervalCollection
     */
    protected $intervals;

    /**
     * GroupByQuery constructor.
     *
     * @param string                                         $dataSource
     * @param \Level23\Druid\Collections\DimensionCollection $dimensions
     * @param \Level23\Druid\Collections\IntervalCollection  $intervals
     * @param null|array|AggregationCollection               $aggregations
     * @param string|\Level23\Druid\Types\Granularity        $granularity
     */
    public function __construct(
        string $dataSource,
        DimensionCollection $dimensions,
        IntervalCollection $intervals,
        $aggregations = null,
        $granularity = 'all'
    ) {
        if (is_string($granularity) && !Granularity::isValid($granularity)) {
            throw new InvalidArgumentException(
                'The given granularity is invalid: ' . $granularity . '. ' .
                'Allowed are: ' . implode(',', Granularity::values())
            );
        }

        $this->dataSource  = $dataSource;
        $this->dimensions  = $dimensions;
        $this->granularity = $granularity;
        $this->intervals   = $intervals;

        if ($aggregations) {
            $this->setAggregations($aggregations);
        }
    }

    /**
     * Return the query in array format so we can fire it to druid.
     *
     * @return array
     */
    public function getQuery(): array
    {
        $query = [
            'queryType'   => 'groupBy',
            'dataSource'  => $this->dataSource,
            'intervals'   => $this->intervals->toArray(),
            'dimensions'  => $this->dimensions->toArray(),
            'granularity' => $this->granularity,
        ];

        if ($this->filter) {
            $query['filter'] = $this->filter->getFilter();
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
            $query['having'] = $this->having->getHavingFilter();
        }

        if ($this->context) {
            $query['context'] = $this->context->getContext();
        }

        if ($this->limit) {
            $query['limitSpec'] = $this->limit->getLimitForQuery();
        }

        // @todo: subtotalsSpec

        return $query;
    }

    /**
     * @param \Level23\Druid\Filters\FilterInterface $filter
     *
     * @return GroupByQuery
     */
    public function setFilter(FilterInterface $filter): GroupByQuery
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return \Level23\Druid\Filters\FilterInterface|null
     */
    public function getFilter(): ?FilterInterface
    {
        return $this->filter;
    }

    /**
     * @param \Level23\Druid\Collections\AggregationCollection|array $aggregations
     *
     * @return GroupByQuery
     */
    public function setAggregations($aggregations): GroupByQuery
    {
        if (is_array($aggregations)) {
            $aggregations = AggregationCollection::make($aggregations);
        }
        $this->aggregations = $aggregations;

        return $this;
    }

    /**
     * @return \Level23\Druid\Collections\AggregationCollection|null
     */
    public function getAggregations(): ?AggregationCollection
    {
        return $this->aggregations;
    }

    /**
     * @param \Level23\Druid\Collections\PostAggregationCollection|array $postAggregations
     *
     * @return GroupByQuery
     */
    public function setPostAggregations($postAggregations): GroupByQuery
    {
        if (is_array($postAggregations)) {
            $postAggregations = PostAggregationCollection::make($postAggregations);
        }

        $this->postAggregations = $postAggregations;

        return $this;
    }

    /**
     * @return \Level23\Druid\Collections\PostAggregationCollection|null
     */
    public function getPostAggregations(): ?PostAggregationCollection
    {
        return $this->postAggregations;
    }

    /**
     * @param \Level23\Druid\HavingFilters\HavingFilterInterface $having
     *
     * @return GroupByQuery
     */
    public function setHaving(HavingFilterInterface $having): GroupByQuery
    {
        $this->having = $having;

        return $this;
    }

    /**
     * @return \Level23\Druid\HavingFilters\HavingFilterInterface|null
     */
    public function getHaving(): ?HavingFilterInterface
    {
        return $this->having;
    }

    /**
     * @param \Level23\Druid\Context\ContextInterface $context
     */
    public function setContext(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * @return \Level23\Druid\Context\ContextInterface|null
     */
    public function getContext(): ?ContextInterface
    {
        return $this->context;
    }

    /**
     * @param \Level23\Druid\Limits\LimitInterface|int $limit
     *
     * @return GroupByQuery
     */
    public function setLimit($limit): GroupByQuery
    {
        if (is_numeric($limit)) {
            $limit = new Limit($limit);
        }

        if (!$limit instanceof LimitInterface) {
            throw new InvalidArgumentException(
                'The given limit should be numeric or an instance of LimitInterface'
            );
        }

        $this->limit = $limit;

        return $this;
    }

    /**
     * @return \Level23\Druid\Limits\LimitInterface|null
     */
    public function getLimit(): ?LimitInterface
    {
        return $this->limit;
    }
}