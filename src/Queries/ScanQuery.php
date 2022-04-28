<?php
declare(strict_types=1);

namespace Level23\Druid\Queries;

use Level23\Druid\Context\QueryContext;
use Level23\Druid\Types\OrderByDirection;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Types\ScanQueryResultFormat;
use Level23\Druid\Responses\ScanQueryResponse;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\DataSources\DataSourceInterface;

/**
 * Class ScanQuery
 *
 * @see     https://druid.apache.org/docs/latest/querying/scan-query.html
 * @package Level23\Druid\Queries
 */
class ScanQuery implements QueryInterface
{
    protected DataSourceInterface $dataSource;

    protected IntervalCollection $intervals;

    protected string $resultFormat = ScanQueryResultFormat::NORMAL_LIST;

    /**
     * How many rows buffered before return to client. Default is 20480
     *
     * @var int
     */
    protected int $batchSize;

    /**
     * Return results consistent with the legacy "scan-query" contrib extension. Defaults to the value set by
     * druid.query.scan.legacy, which in turn defaults to false. See Legacy mode for details.
     *
     * @var bool
     */
    protected bool $legacy;

    /**
     * The ordering of returned rows based on timestamp. "ascending", "descending" are supported. When not supplied,
     * "none" is used. Currently, "ascending" and "descending" are only supported for queries where the __time column
     * is included in the columns field and the requirements outlined in the time ordering section are met.
     *
     * @var string
     */
    protected string $order;

    /**
     * How many rows to return. If not specified, all rows will be returned.
     *
     * @var int
     */
    protected int $limit;

    /**
     * Skip this many rows when returning results.
     *
     * @var int
     */
    protected int $offset;

    protected ?QueryContext $context;

    protected ?FilterInterface $filter;

    /**
     * A String array of dimensions and metrics to scan. If left empty, all dimensions and metrics are returned.
     *
     * @var array|string[]
     */
    protected array $columns = [];

    public function __construct(DataSourceInterface $dataSource, IntervalCollection $intervals)
    {
        $this->dataSource = $dataSource;
        $this->intervals  = $intervals;
    }

    /**
     * Return the query in array format, so we can fire it to druid.
     *
     * @return array<string,string|array<mixed>|int|bool>
     */
    public function toArray(): array
    {
        $result = [
            'queryType'    => 'scan',
            'dataSource'   => $this->dataSource->toArray(),
            'intervals'    => $this->intervals->toArray(),
            'resultFormat' => $this->resultFormat,
            'columns'      => $this->columns,
        ];

        if (isset($this->filter)) {
            $result['filter'] = $this->filter->toArray();
        }

        if (isset($this->batchSize)) {
            $result['batchSize'] = $this->batchSize;
        }

        if (isset($this->limit)) {
            $result['limit'] = $this->limit;
        }

        if (isset($this->offset)) {
            $result['offset'] = $this->offset;
        }

        if (isset($this->legacy)) {
            $result['legacy'] = $this->legacy;
        }

        if (isset($this->context)) {
            $result['context'] = $this->context->toArray();
        }

        if (isset($this->order)) {
            $result['order'] = $this->order;
        }

        return $result;
    }

    /**
     * Parse the response into something we can return to the user.
     *
     * @param array<string|int,string|int|array<mixed>> $response
     *
     * @return ScanQueryResponse
     */
    public function parseResponse(array $response): ScanQueryResponse
    {
        return new ScanQueryResponse($response);
    }

    /**
     * How the results are represented. Use one of the ScanQueryResultFormat constants
     *
     * @param string $resultFormat
     */
    public function setResultFormat(string $resultFormat): void
    {
        $this->resultFormat = ScanQueryResultFormat::validate($resultFormat);
    }

    /**
     * How many rows buffered before return to client. Default is 20480
     *
     * @param int $batchSize
     */
    public function setBatchSize(int $batchSize): void
    {
        $this->batchSize = $batchSize;
    }

    /**
     * How many rows to return. If not specified, all rows will be returned.
     *
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * Skip this many rows when returning results. Skipped rows will still need to be generated internally and then
     * discarded, meaning that raising offsets to high values can cause queries to use additional resources.
     *
     * Together, "limit" and "offset" can be used to implement pagination. However, note that if the underlying
     * datasource is modified in between page fetches in ways that affect overall query results, then the
     * different pages will not necessarily align with each other.
     *
     * @param int $offset
     */
    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @param bool $legacy
     */
    public function setLegacy(bool $legacy): void
    {
        $this->legacy = $legacy;
    }

    /**
     * @param \Level23\Druid\Context\QueryContext $context
     */
    public function setContext(QueryContext $context): void
    {
        $this->context = $context;
    }

    /**
     * @param \Level23\Druid\Filters\FilterInterface $filter
     */
    public function setFilter(FilterInterface $filter): void
    {
        $this->filter = $filter;
    }

    /**
     * The ordering of returned rows based on timestamp. "ascending", "descending", and "none" (default) are supported.
     * Currently, "ascending" and "descending" are only supported for queries where the __time column is included in
     * the columns field and the requirements outlined in the time ordering section are met.
     *
     * @param string $order
     */
    public function setOrder(string $order): void
    {
        $this->order = OrderByDirection::validate($order);
    }

    /**
     * A String array of dimensions and metrics to scan. If left empty, all dimensions and metrics are returned.
     *
     * @param array|string[] $columns
     */
    public function setColumns(array $columns): void
    {
        $this->columns = $columns;
    }
}