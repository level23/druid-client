<?php
declare(strict_types=1);

namespace Level23\Druid\Queries;

use InvalidArgumentException;
use Level23\Druid\Types\Granularity;
use Level23\Druid\Context\QueryContext;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Responses\SelectQueryResponse;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Collections\DimensionCollection;
use Level23\Druid\DataSources\DataSourceInterface;

/**
 * Class SelectQuery
 *
 * We encourage you to use the Scan query type rather than Select whenever possible. In
 * situations involving larger numbers of segments, the Select query can have very high memory and performance
 * overhead. The Scan query does not have this issue. The major difference between the two is that the Scan query does
 * not support pagination. However, the Scan query type is able to return a virtually unlimited number of results even
 * without pagination, making it unnecessary in many cases.
 *
 * @see     https://druid.apache.org/docs/latest/querying/select-query.html
 * @package Level23\Druid\Queries
 */
class SelectQuery implements QueryInterface
{
    protected DataSourceInterface $dataSource;

    protected IntervalCollection $intervals;

    protected bool $descending = false;

    protected ?FilterInterface $filter = null;

    protected ?DimensionCollection $dimensions = null;

    protected string $granularity = Granularity::ALL;

    /**
     * @var array|string[]
     */
    protected array $metrics;

    protected ?QueryContext $context = null;

    protected int $threshold;

    /** @var array<string,int>|null */
    protected ?array $pagingIdentifier = null;

    /**
     * SelectQuery constructor.
     *
     * @param DataSourceInterface      $dataSource A DataSourceInterface defining the data source to query, very similar
     *                                             to a table in a relational database.
     * @param IntervalCollection       $intervals  This defines the time ranges to run the query over.
     * @param int                      $threshold  The threshold determines how many hits are returned, with each hit
     *                                             indexed by an offset. When descending is true, the offset will be
     *                                             negative value.
     * @param DimensionCollection|null $dimensions A list of dimensions to select. If left empty, all dimensions are
     *                                             returned.
     * @param array|string[]           $metrics    A String array of metrics to select. If left empty, all metrics are
     *                                             returned.
     * @param bool                     $descending Whether to make descending ordered result. Default is
     *                                             false(ascending). When this is true, page identifier and offsets
     *                                             will be negative value.
     */
    public function __construct(
        DataSourceInterface $dataSource,
        IntervalCollection $intervals,
        int $threshold,
        ?DimensionCollection $dimensions = null,
        array $metrics = [],
        bool $descending = false
    ) {
        $this->dataSource = $dataSource;
        $this->intervals  = $intervals;
        $this->descending = $descending;
        $this->dimensions = $dimensions;
        $this->metrics    = $metrics;
        $this->threshold  = $threshold;
    }

    /**
     * Return the query in array format, so we can fire it to druid.
     *
     * @return array<string,string|array<mixed>|bool>
     */
    public function toArray(): array
    {
        $result = [
            'queryType'   => 'select',
            'dataSource'  => $this->dataSource->toArray(),
            'intervals'   => $this->intervals->toArray(),
            'descending'  => $this->descending,
            'dimensions'  => $this->dimensions ? $this->dimensions->toArray() : [],
            'metrics'     => $this->metrics,
            'granularity' => $this->granularity,
            'pagingSpec'  => [
                'pagingIdentifiers' => $this->pagingIdentifier,
                'threshold'         => $this->threshold,
            ],
        ];

        if ($this->context) {
            $result['context'] = $this->context->toArray();
        }

        if ($this->filter) {
            $result['filter'] = $this->filter->toArray();
        }

        return $result;
    }

    /**
     * Parse the response into something we can return to the user.
     *
     * @param array<string|int,string|int|array<mixed>> $response
     *
     * @return SelectQueryResponse
     */
    public function parseResponse(array $response): SelectQueryResponse
    {
        return new SelectQueryResponse($response);
    }

    /**
     * @param \Level23\Druid\Filters\FilterInterface $filter
     */
    public function setFilter(FilterInterface $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * Defines the granularity of the query. Default is Granularity.ALL.
     *
     * @param string $granularity
     *
     * @throws InvalidArgumentException
     */
    public function setGranularity(string $granularity): void
    {
        $this->granularity = Granularity::validate($granularity);
    }

    /**
     * @param \Level23\Druid\Context\QueryContext $context
     */
    public function setContext(QueryContext $context): void
    {
        $this->context = $context;
    }

    /**
     *
     * @param array<string,int> $pagingIdentifier
     */
    public function setPagingIdentifier(array $pagingIdentifier): void
    {
        $this->pagingIdentifier = $pagingIdentifier;
    }
}