<?php
declare(strict_types=1);

namespace Level23\Druid\Queries;

use Level23\Druid\Types\SortingOrder;
use Level23\Druid\Context\QueryContext;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Responses\SearchQueryResponse;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\SearchFilters\SearchFilterInterface;

class SearchQuery implements QueryInterface
{
    /**
     * @var string
     */
    protected $dataSource;

    /**
     * @var string
     */
    protected $granularity;

    /**
     * @var \Level23\Druid\Collections\IntervalCollection
     */
    protected $intervals;

    /**
     * @var \Level23\Druid\Filters\FilterInterface|null
     */
    protected $filter;

    /**
     * @var int|null
     */
    protected $limit;

    /**
     * The dimensions to run the search over. Excluding this means the search is run over all dimensions.
     *
     * @var array|string[]
     */
    protected $dimensions = [];

    /**
     * @var string
     */
    protected $sort = SortingOrder::LEXICOGRAPHIC;

    /**
     * @var \Level23\Druid\Context\QueryContext|null
     */
    protected $context;

    /**
     * @var \Level23\Druid\SearchFilters\SearchFilterInterface
     */
    protected $searchFilter;

    public function __construct(
        string $dataSource,
        string $granularity,
        IntervalCollection $intervals,
        SearchFilterInterface $searchFilter
    ) {
        $this->dataSource   = $dataSource;
        $this->granularity  = $granularity;
        $this->intervals    = $intervals;
        $this->searchFilter = $searchFilter;
    }

    /**
     * Return the query in array format so we can fire it to druid.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'queryType'   => 'search',
            'dataSource'  => $this->dataSource,
            'granularity' => $this->granularity,
            'intervals'   => $this->intervals->toArray(),
            'sort'        => ['type' => $this->sort],
            'query'       => $this->searchFilter->toArray(),
        ];

        if ($this->filter) {
            $result['filter'] = $this->filter->toArray();
        }

        if ($this->limit) {
            $result['limit'] = $this->limit;
        }

        if (count($this->dimensions) > 0) {
            $result['searchDimensions'] = $this->dimensions;
        }

        if ($this->context) {
            $result['context'] = $this->context->toArray();
        }

        return $result;
    }

    /**
     * Parse the response into something we can return to the user.
     *
     * @param array $response
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
     * @param string $sort
     */
    public function setSort(string $sort): void
    {
        $this->sort = SortingOrder::validate($sort);
    }

    /**
     * @param \Level23\Druid\Context\QueryContext $context
     */
    public function setContext(QueryContext $context): void
    {
        $this->context = $context;
    }
}