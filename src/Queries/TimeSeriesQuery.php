<?php
declare(strict_types=1);

namespace Level23\Druid\Queries;

use Level23\Druid\Types\Granularity;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Context\ContextInterface;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\DataSources\DataSourceInterface;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Responses\TimeSeriesQueryResponse;
use Level23\Druid\Collections\VirtualColumnCollection;
use Level23\Druid\Collections\PostAggregationCollection;

class TimeSeriesQuery implements QueryInterface
{
    protected DataSourceInterface $dataSource;

    protected IntervalCollection $intervals;

    protected string $granularity;

    protected ?FilterInterface $filter = null;

    protected ?VirtualColumnCollection $virtualColumns = null;

    protected ?AggregationCollection $aggregations = null;

    protected ?PostAggregationCollection $postAggregations = null;

    protected ?ContextInterface $context = null;

    protected bool $descending = false;

    protected string $timeOutputName = 'timestamp';

    /**
     * Not documented (yet), but supported since 0.13.0
     * It is revealed also in a query like:
     * `explain plan for select floor(__time to day), count(*) from "dataSource" group by 1 limit 2;`
     *
     * @see https://github.com/apache/incubator-druid/pull/5931
     * @var int|null
     */
    protected ?int $limit = null;

    /**
     * TimeSeriesQuery constructor.
     *
     * @param DataSourceInterface $dataSource
     * @param IntervalCollection  $intervals
     * @param string              $granularity
     */
    public function __construct(
        DataSourceInterface $dataSource,
        IntervalCollection $intervals,
        string $granularity = 'all'
    ) {
        $this->dataSource  = $dataSource;
        $this->intervals   = $intervals;
        $this->granularity = Granularity::validate($granularity);
    }

    /**
     * Return the query in array format, so we can fire it to druid.
     *
     * @return array<string,string|array<mixed>|bool|int>
     */
    public function toArray(): array
    {
        $result = [
            'queryType'   => 'timeseries',
            'dataSource'  => $this->dataSource->toArray(),
            'descending'  => $this->descending,
            'intervals'   => $this->intervals->toArray(),
            'granularity' => $this->granularity,
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

        if ($this->limit) {
            $result['limit'] = $this->limit;
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
     * @param bool $descending
     */
    public function setDescending(bool $descending): void
    {
        $this->descending = $descending;
    }

    /**
     * Parse the response into something we can return to the user.
     *
     * @param array<string|int,array<mixed>|int|string> $response
     *
     * @return TimeSeriesQueryResponse
     */
    public function parseResponse(array $response): TimeSeriesQueryResponse
    {
        return new TimeSeriesQueryResponse($response, $this->timeOutputName);
    }

    /**
     * Set the name which will be used in the result set to store the timestamp in.
     *
     * @param string $timeOutputName
     */
    public function setTimeOutputName(string $timeOutputName): void
    {
        $this->timeOutputName = $timeOutputName;
    }

    /**
     * @param \Level23\Druid\Collections\VirtualColumnCollection $virtualColumns
     */
    public function setVirtualColumns(VirtualColumnCollection $virtualColumns): void
    {
        $this->virtualColumns = $virtualColumns;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }
}