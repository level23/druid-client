<?php
declare(strict_types=1);

namespace Level23\Druid\Queries;

use Level23\Druid\Types\Granularity;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Context\ContextInterface;
use Level23\Druid\Responses\TopNQueryResponse;
use Level23\Druid\Dimensions\DimensionInterface;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\DataSources\DataSourceInterface;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Collections\VirtualColumnCollection;
use Level23\Druid\Collections\PostAggregationCollection;

class TopNQuery implements QueryInterface
{
    protected DataSourceInterface $dataSource;

    protected IntervalCollection $intervals;

    protected string $granularity;

    protected DimensionInterface $dimension;

    protected ?VirtualColumnCollection $virtualColumns = null;

    protected int $threshold;

    protected string $metric;

    protected ?FilterInterface $filter = null;

    protected ?AggregationCollection $aggregations = null;

    protected ?PostAggregationCollection $postAggregations = null;

    protected ?ContextInterface $context = null;

    protected bool $descending = true;

    /**
     * TopNQuery constructor.
     *
     * @param DataSourceInterface $dataSource
     * @param IntervalCollection  $intervals
     * @param DimensionInterface  $dimension
     * @param int                 $threshold
     * @param string              $metric
     * @param string              $granularity
     */
    public function __construct(
        DataSourceInterface $dataSource,
        IntervalCollection $intervals,
        DimensionInterface $dimension,
        int $threshold,
        string $metric,
        string $granularity = 'all'
    ) {
        $this->dataSource  = $dataSource;
        $this->intervals   = $intervals;
        $this->granularity = Granularity::validate($granularity);
        $this->dimension   = $dimension;
        $this->threshold   = $threshold;
        $this->metric      = $metric;
    }

    /**
     * Return the query in array format, so we can fire it to druid.
     *
     * @return array<string,string|int|array<mixed>>
     */
    public function toArray(): array
    {
        $metricSpec = [
            'type'   => 'numeric',
            'metric' => $this->metric,
        ];

        if (!$this->descending) {
            $metricSpec = [
                'type'   => 'inverted',
                'metric' => $metricSpec,
            ];
        }

        $result = [
            'queryType'   => 'topN',
            'dataSource'  => $this->dataSource->toArray(),
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
     * @param array<string|int,array<mixed>|int|string> $response
     *
     * @return TopNQueryResponse
     */
    public function parseResponse(array $response): TopNQueryResponse
    {
        return new TopNQueryResponse($response);
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

