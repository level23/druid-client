<?php
declare(strict_types=1);

namespace Level23\Druid\Limits;

use Level23\Druid\Collections\OrderByCollection;
use Level23\Druid\OrderBy\OrderByInterface;

class Limit implements LimitInterface
{
    /**
     * @var int
     */
    protected $limit;

    /**
     * @var \Level23\Druid\Collections\OrderByCollection|null
     */
    protected $orderBy;

    public function __construct(int $limit, OrderByCollection $orderBy = null)
    {
        $this->limit   = $limit;
        $this->orderBy = $orderBy;
    }

    /**
     * Return the limit in array format so that it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'type'    => 'default',
            'limit'   => $this->limit,
            'columns' => ($this->orderBy ? $this->orderBy->toArray() : []),
        ];

        return $result;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit)
    {
        $this->limit = $limit;
    }

    /**
     * Add an order by field to the collection.
     *
     * @param \Level23\Druid\OrderBy\OrderByInterface $orderBy
     */
    public function addOrderBy(OrderByInterface $orderBy)
    {
        if ($this->orderBy === null) {
            $this->orderBy = new OrderByCollection();
        }
        $this->orderBy->add($orderBy);
    }

    /**
     * Get the limit which is currently configured.
     *
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Return the fields which are used to sort the result by.
     *
     * @return \Level23\Druid\Collections\OrderByCollection
     */
    public function getOrderByCollection(): OrderByCollection
    {
        if (is_null($this->orderBy)) {
            return new OrderByCollection();
        }

        return $this->orderBy;
    }
}