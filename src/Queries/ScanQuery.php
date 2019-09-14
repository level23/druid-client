<?php
declare(strict_types=1);

namespace Level23\Druid\Queries;

use Level23\Druid\Context\QueryContext;
use Level23\Druid\Types\OrderByDirection;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Types\ScanQueryResultFormat;
use Level23\Druid\Responses\ScanQueryResponse;
use Level23\Druid\Collections\IntervalCollection;

/**
 * Class ScanQuery
 *
 * @see     https://druid.apache.org/docs/latest/querying/scan-query.html
 * @package Level23\Druid\Queries
 */
class ScanQuery implements QueryInterface
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
     * @var string
     */
    protected $resultFormat = ScanQueryResultFormat::NORMAL_LIST;

    /**
     * How many rows buffered before return to client. Default is 20480
     *
     * @var int
     */
    protected $batchSize;

    /**
     * Return results consistent with the legacy "scan-query" contrib extension. Defaults to the value set by
     * druid.query.scan.legacy, which in turn defaults to false. See Legacy mode for details.
     *
     * @var bool
     */
    protected $legacy;

    /**
     * The ordering of returned rows based on timestamp. "ascending", "descending" are supported. When not supplied,
     * "none" is used. Currently, "ascending" and "descending" are only supported for queries where the __time column
     * is included in the columns field and the requirements outlined in the time ordering section are met.
     *
     * @var string.
     */
    protected $order;

    /**
     * How many rows to return. If not specified, all rows will be returned.
     *
     * @var int
     */
    protected $limit;

    /**
     * @var \Level23\Druid\Context\QueryContext|null
     */
    protected $context;

    /**
     * @var \Level23\Druid\Filters\FilterInterface|null
     */
    protected $filter;

    /**
     * A String array of dimensions and metrics to scan. If left empty, all dimensions and metrics are returned.
     *
     * @var array|string[]
     */
    protected $columns = [];

    public function __construct(string $dataSource, IntervalCollection $intervals)
    {
        $this->dataSource = $dataSource;
        $this->intervals  = $intervals;
    }

    /**
     * Return the query in array format so we can fire it to druid.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'queryType'    => 'scan',
            'dataSource'   => $this->dataSource,
            'intervals'    => $this->intervals->toArray(),
            'resultFormat' => $this->resultFormat,
            'columns'      => $this->columns,
        ];

        if ($this->filter) {
            $result['filter'] = $this->filter->toArray();
        }

        if ($this->batchSize !== null) {
            $result['batchSize'] = $this->batchSize;
        }

        if ($this->limit !== null) {
            $result['limit'] = $this->limit;
        }

        if ($this->legacy !== null) {
            $result['legacy'] = $this->legacy;
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