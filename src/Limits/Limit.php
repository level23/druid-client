<?php
declare(strict_types=1);

namespace Level23\Druid\Limits;

use Level23\Druid\OrderBy\OrderByInterface;
use Level23\Druid\Collections\OrderByCollection;

class Limit implements LimitInterface
{
    protected ?int $limit;

    protected ?OrderByCollection $orderBy;

    protected ?int $offset;

    public function __construct(int $limit = null, OrderByCollection $orderBy = null, int $offset = null)
    {
        $this->limit   = $limit;
        $this->orderBy = $orderBy;
        $this->offset  = $offset;
    }

    /**
     * Return the limit in array format so that it can be used in a druid query.
     *
     * @return array<string,string|array<array<string,string>>|int>
     */
    public function toArray(): array
    {
        $result = [
            'type'    => 'default',
            'columns' => ($this->orderBy ? $this->orderBy->toArray() : []),
        ];
        if ($this->limit !== null) {
            $result['limit'] = $this->limit;
        }
        if ($this->offset !== null) {
            $result['offset'] = $this->offset;
        }

        return $result;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * Add an order by field to the collection.
     *
     * @param \Level23\Druid\OrderBy\OrderByInterface $orderBy
     */
    public function addOrderBy(OrderByInterface $orderBy): void
    {
        if ($this->orderBy === null) {
            $this->orderBy = new OrderByCollection();
        }
        $this->orderBy->add($orderBy);
    }

    /**
     * Get the limit which is currently configured.
     *
     * @return int|null
     */
    public function getLimit(): ?int
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

    /**
     * @param int $offset
     */
    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @return int|null
     */
    public function getOffset(): ?int
    {
        return $this->offset;
    }
}