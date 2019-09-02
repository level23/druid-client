<?php
declare(strict_types=1);

namespace Level23\Druid\Queries;

use Level23\Druid\Types\Granularity;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Context\ContextInterface;
use Level23\Druid\Dimensions\DimensionInterface;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Collections\VirtualColumnCollection;
use Level23\Druid\Collections\PostAggregationCollection;

class TopNQuery implements QueryInterface
{
    /**
     * @var string
     */
    protected $dataSource;

    /**
     * @var \Level23\Druid\Collections\IntervalCollection
     */
    protected $intervals;

    /**
     * @var \Level23\Druid\Types\Granularity|string
     */
    protected $granularity;

    /**
     * @var \Level23\Druid\Dimensions\DimensionInterface
     */
    protected $dimension;

    /**
     * @var \Level23\Druid\Collections\VirtualColumnCollection|null
     */
    protected $virtualColumns;

    /**
     * @var int
     */
    protected $threshold;

    /**
     * @var string
     */
    protected $metric;

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
     * @var \Level23\Druid\Context\ContextInterface|null
     */
    protected $context;

    /**
     * @var bool
     */
    protected $descending = false;

    /**
     * TopNQuery constructor.
     *
     * @param string             $dataSource
     * @param IntervalCollection $intervals
     * @param DimensionInterface $dimension
     * @param int                $threshold
     * @param string             $metric
     * @param string|Granularity $granularity
     */
    public function __construct(
        string $dataSource,
        IntervalCollection $intervals,
        DimensionInterface $dimension,
        int $threshold,
        string $metric,
        $granularity = 'all'
    ) {
        $this->dataSource  = $dataSource;
        $this->intervals   = $intervals;
        $this->granularity = Granularity::validate($granularity);
        $this->dimension   = $dimension;
        $this->threshold   = $threshold;
        $this->metric      = $metric;
    }

    /**
     * Return the query in array format so we can fire it to druid.
     *
     * @return array
     */
    public function toArray(): array
    {
        $metricSpec = [
            'type'   => 'numeric',
            'metric' => $this->metric,
        ];

        if ($this->descending) {
            $metricSpec = [
                'type'   => 'inverted',
                'metric' => $metricSpec,
            ];
        }

        $result = [
            'queryType'   => 'topN',
            'dataSource'  => $this->dataSource,
            'intervals'   => $this->intervals->toArray(),
            'granularity' => $this->granularity,
            'dimension'   => $this->dimension->toArray(),
            'threshold'   => $this->threshold,
            'metric'      => $metricSpec,
        ];

        if ($this->filter) {
            $result['filter'] = $this->filter->toArray();
        }

        if ($this->virtualColumns) {
            $result['virtualColumns'] = $this->virtualColumns->toArray();
        }

        if ($this->aggregations) {
            $result['aggregations'] = $this->aggregations->toArray();
        }

        if ($this->postAggregations) {
            $result['postAggregations'] = $this->postAggregations->toArray();
        }

        if ($this->context) {
            $result['context'] = $this->context->toArray();
        }

        return $result;
    }

    /**
     * @param \Level23\Druid\Filters\FilterInterface $filter
     */
    public function setFilter(FilterInterface $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * @param \Level23\Druid\Collections\AggregationCollection $aggregations
     */
    public function setAggregations(AggregationCollection $aggregations): void
    {
        $this->aggregations = $aggregations;
    }

    /**
     * @param \Level23\Druid\Collections\PostAggregationCollection $postAggregations
     */
    public function setPostAggregations(PostAggregationCollection $postAggregations): void
    {
        $this->postAggregations = $postAggregations;
    }

    /**
     * @param \Level23\Druid\Context\ContextInterface $context
     */
    public function setContext(ContextInterface $context): void
    {
        $this->context = $context;
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
            return $row['result'];
        }, $response);
    }

    /**
     * @param \Level23\Druid\Collections\VirtualColumnCollection $virtualColumns
     */
    public function setVirtualColumns(VirtualColumnCollection $virtualColumns): void
    {
        $this->virtualColumns = $virtualColumns;
    }

    /**
     * @param bool $descending
     */
    public function setDescending(bool $descending): void
    {
        $this->descending = $descending;
    }
}

