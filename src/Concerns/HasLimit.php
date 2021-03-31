<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Level23\Druid\Limits\Limit;
use Level23\Druid\OrderBy\OrderBy;
use Level23\Druid\Types\SortingOrder;
use Level23\Druid\Limits\LimitInterface;
use Level23\Druid\Types\OrderByDirection;

trait HasLimit
{
    /**
     * @var \Level23\Druid\Limits\LimitInterface|null
     */
    protected $limit;

    /**
     * @var string|null
     */
    protected $direction;

    /**
     * We can only order by fields if there is a limit specified (....., I know... ).
     * When the user applies an order by, but does not specify a limit, we will use this
     * high number as a limit.
     *
     * @var int
     */
    public static $DEFAULT_MAX_LIMIT = 999999;

    /**
     * Limit out result by N records.
     * The "offset" parameter tells Druid to skip this many rows when returning results.
     *
     * @param int      $limit
     * @param int|null $offset
     *
     * @return $this
     */
    public function limit(int $limit, int $offset = null)
    {
        if ($this->limit instanceof LimitInterface) {
            $this->limit->setLimit($limit);
        } else {
            $this->limit = new Limit($limit);
        }

        if ($offset !== null) {
            $this->limit->setOffset($offset);
        }

        return $this;
    }

    /**
     * Sort the result. This only applies for GroupBy and TopN Queries.
     * You should use `orderByDirection()` for TimeSeries, Select and Scan Queries.
     *
     * @param string $dimensionOrMetric The dimension or metric where you want to order by.
     * @param string $direction         The direction of your order. Default is "asc".
     * @param string $sortingOrder      The algorithm used to order the result.
     *
     * @return $this
     */
    public function orderBy(
        string $dimensionOrMetric,
        string $direction = OrderByDirection::ASC,
        string $sortingOrder = SortingOrder::LEXICOGRAPHIC
    ) {
        $order = new OrderBy($dimensionOrMetric, $direction, $sortingOrder);

        if (!$this->limit) {
            $this->limit = new Limit();
        }

        $this->limit->addOrderBy($order);

        return $this;
    }

    /**
     * In which order should we return the result.
     * This only applies to TimeSeries, Select and Scan Queries. Use `orderBy()` For GroupBy and TopN Queries.
     *
     * @param string $direction The direction of your order.
     *
     * @return $this
     */
    public function orderByDirection(string $direction = OrderByDirection::DESC)
    {
        $this->direction = OrderByDirection::validate($direction);

        return $this;
    }

    /**
     * @return \Level23\Druid\Limits\LimitInterface|null
     */
    public function getLimit(): ?LimitInterface
    {
        return $this->limit;
    }
}