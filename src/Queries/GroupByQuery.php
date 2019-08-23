<?php
declare(strict_types=1);

namespace Level23\Druid\Queries;

use InvalidArgumentException;
use Level23\Druid\Limits\Limit;
use Level23\Druid\Types\Granularity;
use Level23\Druid\Limits\LimitInterface;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Context\ContextInterface;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Collections\DimensionCollection;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Collections\VirtualColumnCollection;
use Level23\Druid\HavingFilters\HavingFilterInterface;
use Level23\Druid\Collections\PostAggregationCollection;

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

        // @todo: subtotalsSpec

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
    public function setPostAggregations($postAggregations)
    {
        if (is_array($postAggregations)) {
            $postAggregations = new PostAggregationCollection(...$postAggregations);
        }

        $this->postAggregations = $postAggregations;
    }

    /**
     * @param \Level23\Druid\HavingFilters\HavingFilterInterface $having
     */
    public function setHaving(HavingFilterInterface $having)
    {
        $this->having = $having;
    }

    /**
     * @param \Level23\Druid\Context\ContextInterface $context
     */
    public function setContext(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * @param \Level23\Druid\Limits\LimitInterface|int $limit
     */
    public function setLimit($limit)
    {
        if (is_numeric($limit)) {
            $limit = new Limit($limit);
        }

        $this->limit = $limit;
    }

    /**
     * Parse the response into something we can return to the user.
     *
     * @param array $response
     *
     * @return array
     */
    public function parseResponse(array $response): array
    {
        return array_map(function ($row) {
            return $row['event'];
        }, $response);
    }

    /**
     * @param \Level23\Druid\Collections\VirtualColumnCollection $virtualColumns
     */
    public function setVirtualColumns(VirtualColumnCollection $virtualColumns): void
    {
        $this->virtualColumns = $virtualColumns;
    }
}