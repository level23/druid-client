<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Level23\Druid\Limits\Limit;
use Level23\Druid\OrderBy\OrderBy;
use Level23\Druid\Limits\LimitInterface;
use Level23\Druid\Types\OrderByDirection;

trait HasLimit
{
    /**
     * @var \Level23\Druid\Limits\LimitInterface|null
     */
    protected $limit;

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
     *
     * @param int $limit
     *
     * @return $this
     */
    public function limit(int $limit)
    {
        if ($this->limit instanceof LimitInterface) {
            $this->limit->setLimit($limit);
        } else {
            $this->limit = new Limit($limit);
        }

        return $this;
    }

    /**
     * @param string $dimension
     * @param string $direction
     * @param string $dimensionOrder
     *
     * @return $this
     */
    public function orderBy(string $dimension, string $direction, string $dimensionOrder = 'lexicographic')
    {
        $direction = strtolower($direction);
        if ($direction == 'asc') {
            $direction = OrderByDirection::ASC;
        } elseif ($direction == 'desc') {
            $direction = OrderByDirection::DESC;
        }

        $order = new OrderBy($dimension, $direction, $dimensionOrder);

        if (!$this->limit) {
            $this->limit = new Limit(self::$DEFAULT_MAX_LIMIT);
        }

        $this->limit->addOrderBy($order);

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