<?php
declare(strict_types=1);

namespace Level23\Druid\Aggregations;

use Level23\Druid\Filters\FilterInterface;

/**
 * Class FilteredAggregator
 *
 * A filtered aggregator wraps any given aggregator,
 * but only aggregates the values for which the given dimension filter matches.
 *
 * This makes it possible to compute the results of a filtered and an unfiltered
 * aggregation simultaneously, without having to issue multiple queries,
 * and use both results as part of post-aggregations.
 *
 * Note: If only the filtered results are required,
 * consider putting the filter on the query itself, which will be much faster since
 * it does not require scanning all the data.
 *
 * @package Level23\Druid\Aggregations
 */
class FilteredAggregator implements AggregatorInterface
{
    /**
     * @var \Level23\Druid\Aggregations\AggregatorInterface
     */
    protected $aggregator;

    /**
     * @var \Level23\Druid\Filters\FilterInterface
     */
    protected $filter;

    /**
     * CountAggregator constructor.
     *
     * @param \Level23\Druid\Filters\FilterInterface          $filter
     * @param \Level23\Druid\Aggregations\AggregatorInterface $aggregator
     */
    public function __construct(FilterInterface $filter, AggregatorInterface $aggregator)
    {
        $this->filter     = $filter;
        $this->aggregator = $aggregator;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function getAggregator(): array
    {
        return [
            'type'       => 'filtered',
            'filter'     => $this->filter->getFilter(),
            'aggregator' => $this->aggregator->getAggregator(),
        ];
    }

    /**
     * Return how this aggregation will be outputted in the query results.
     *
     * @return string
     */
    public function getOutputName(): string
    {
        return $this->aggregator->getOutputName();
    }
}