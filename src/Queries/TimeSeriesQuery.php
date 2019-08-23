<?php
declare(strict_types=1);

namespace Level23\Druid\Queries;

use Level23\Druid\Types\Granularity;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Context\ContextInterface;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Collections\VirtualColumnCollection;
use Level23\Druid\Collections\PostAggregationCollection;

class TimeSeriesQuery implements QueryInterface
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
     * @var \Level23\Druid\Filters\FilterInterface|null
     */
    protected $filter;

    /**
     * @var \Level23\Druid\Collections\VirtualColumnCollection|null
     */
    protected $virtualColumns;

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
     * @var string
     */
    protected $timeOutputName = 'timestamp';

    /**
     * TimeSeriesQuery constructor.
     *
     * @param string             $dataSource
     * @param IntervalCollection $intervals
     * @param string|Granularity $granularity
     */
    public function __construct(string $dataSource, IntervalCollection $intervals, $granularity = 'all')
    {
        $this->dataSource  = $dataSource;
        $this->intervals   = $intervals;
        $this->granularity = Granularity::validate($granularity);
    }

    /**
     * Return the query in array format so we can fire it to druid.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'queryType'   => 'timeseries',
            'dataSource'  => $this->dataSource,
            'descending'  => $this->descending,
            'intervals'   => $this->intervals->toArray(),
            'granularity' => $this->granularity,
        ];

        if ($this->filter) {
            $result['filter'] = $this->filter->toArray();
        }

        if ($this->virtualColumns) {
            $query['virtualColumns'] = $this->virtualColumns->toArray();
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
    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return \Level23\Druid\Filters\FilterInterface|null
     */
    public function getFilter(): ?FilterInterface
    {
        return $this->filter;
    }

    /**
     * @param \Level23\Druid\Collections\AggregationCollection $aggregations
     */
    public function setAggregations(AggregationCollection $aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * @return \Level23\Druid\Collections\AggregationCollection|null
     */
    public function getAggregations(): ?AggregationCollection
    {
        return $this->aggregations;
    }

    /**
     * @param \Level23\Druid\Collections\PostAggregationCollection $postAggregations
     */
    public function setPostAggregations(PostAggregationCollection $postAggregations)
    {
        $this->postAggregations = $postAggregations;
    }

    /**
     * @return \Level23\Druid\Collections\PostAggregationCollection|null
     */
    public function getPostAggregations(): ?PostAggregationCollection
    {
        return $this->postAggregations;
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
     * @return bool
     */
    public function isDescending(): bool
    {
        return $this->descending;
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
     * @param array $response
     *
     * @return array
     */
    public function parseResponse(array $response): array
    {
        return array_map(function ($row) {
            $row['result'][$this->timeOutputName] = $row['timestamp'];

            return $row['result'];
        }, $response);
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
}