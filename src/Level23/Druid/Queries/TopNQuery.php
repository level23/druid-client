<?php
declare(strict_types=1);

namespace Level23\Druid\Queries;

use InvalidArgumentException;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Collections\PostAggregationCollection;
use Level23\Druid\Context\ContextInterface;
use Level23\Druid\Dimensions\DimensionInterface;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Types\Granularity;

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
        if (is_string($granularity) && !Granularity::isValid($granularity)) {
            throw new InvalidArgumentException(
                'The given granularity is invalid: ' . $granularity . '. ' .
                'Allowed are: ' . implode(',', Granularity::values())
            );
        }

        $this->dataSource  = $dataSource;
        $this->intervals   = $intervals;
        $this->granularity = $granularity;
        $this->dimension   = $dimension;
        $this->threshold   = $threshold;
        $this->metric      = $metric;
    }

    /**
     * Return the query in array format so we can fire it to druid.
     *
     * @return array
     */
    public function getQuery(): array
    {
        $result = [
            'queryType'   => 'topN',
            'dataSource'  => $this->dataSource,
            'intervals'   => $this->intervals->toArray(),
            'granularity' => $this->granularity,
            'dimension'   => $this->dimension->getDimension(),
            'threshold'   => $this->threshold,
            'metric'      => $this->metric,
        ];

        if ($this->filter) {
            $result['filter'] = $this->filter->getFilter();
        }

        if ($this->aggregations) {
            $query['aggregations'] = $this->aggregations->toArray();
        }

        if ($this->postAggregations) {
            $query['postAggregations'] = $this->postAggregations->toArray();
        }

        if ($this->context) {
            $query['context'] = $this->context->getContext();
        }

        return $result;
    }

    /**
     * @param \Level23\Druid\Filters\FilterInterface $filter
     *
     * @return TopNQuery
     */
    public function setFilter(FilterInterface $filter): TopNQuery
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
     * @param \Level23\Druid\Collections\AggregationCollection $aggregations
     *
     * @return TopNQuery
     */
    public function setAggregations(AggregationCollection $aggregations): TopNQuery
    {
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
     * @param \Level23\Druid\Collections\PostAggregationCollection $postAggregations
     *
     * @return TopNQuery
     */
    public function setPostAggregations(PostAggregationCollection $postAggregations): TopNQuery
    {
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
     * @param \Level23\Druid\Context\ContextInterface $context
     *
     * @return TopNQuery
     */
    public function setContext(ContextInterface $context): TopNQuery
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return \Level23\Druid\Context\ContextInterface|null
     */
    public function getContext(): ?ContextInterface
    {
        return $this->context;
    }
}

