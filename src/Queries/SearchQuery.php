<?php
declare(strict_types=1);

namespace Level23\Druid\Queries;

use Level23\Druid\Types\Granularity;
use Level23\Druid\Types\SortingOrder;
use Level23\Druid\Context\QueryContext;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Responses\SearchQueryResponse;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\DataSources\DataSourceInterface;
use Level23\Druid\SearchFilters\SearchFilterInterface;

class SearchQuery implements QueryInterface
{
    protected DataSourceInterface $dataSource;

    protected Granularity $granularity;

    protected IntervalCollection $intervals;

    protected ?FilterInterface $filter = null;

    protected ?int $limit = null;

    /**
     * The dimensions to run the search over. Excluding this means the search is run over all dimensions.
     *
     * @var array|string[]
     */
    protected array $dimensions = [];

    protected SortingOrder $sort = SortingOrder::LEXICOGRAPHIC;

    protected ?QueryContext $context = null;

    protected SearchFilterInterface $searchFilter;

    public function __construct(
        DataSourceInterface $dataSource,
        string|Granularity $granularity,
        IntervalCollection $intervals,
        SearchFilterInterface $searchFilter
    ) {
        $this->dataSource   = $dataSource;
        $this->granularity  = is_string($granularity) ? Granularity::from(strtolower($granularity)) : $granularity;
        $this->intervals    = $intervals;
        $this->searchFilter = $searchFilter;
    }

    /**
     * Return the query in array format, so we can fire it to druid.
     *
     * @return array<string,string|array<mixed>|int>
     */
    public function toArray(): array
    {
        $result = [
            'queryType'   => 'search',
            'dataSource'  => $this->dataSource->toArray(),
            'granularity' => $this->granularity->value,
            'intervals'   => $this->intervals->toArray(),
            'sort'        => ['type' => $this->sort->value],
            'query'       => $this->searchFilter->toArray(),
        ];

        if ($this->filter) {
            $result['filter'] = $this->filter->toArray();
        }

        if ($this->limit > 0) {
            $result['limit'] = $this->limit;
        }

        if (count($this->dimensions) > 0) {
            $result['searchDimensions'] = $this->dimensions;
        }

        if (isset($this->context)) {
            $context = $this->context->toArray();
            if (sizeof($context) > 0) {
                $result['context'] = $context;
            }
        }

        return $result;
    }

    /**
     * Parse the response into something we can return to the user.
     *
     * @param array<string|int,string|int|array<mixed>> $response
     *
     * @return SearchQueryResponse
     */
    public function parseResponse(array $response): SearchQueryResponse
    {
        return new SearchQueryResponse($response);
    }

    /**
     * @param \Level23\Druid\Filters\FilterInterface $filter
     */
    public function setFilter(FilterInterface $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @param array|string[] $dimensions
     */
    public function setDimensions(array $dimensions): void
    {
        $this->dimensions = $dimensions;
    }

    /**
     * @param string|SortingOrder $sort
     */
    public function setSort(string|SortingOrder $sort): void
    {
        $this->sort = is_string($sort) ? SortingOrder::from(strtolower($sort)) : $sort;
    }

    /**
     * @param \Level23\Druid\Context\QueryContext $context
     */
    public function setContext(QueryContext $context): void
    {
        $this->context = $context;
    }
}