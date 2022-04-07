<?php
declare(strict_types=1);

namespace Level23\Druid\Limits;

use Level23\Druid\OrderBy\OrderByInterface;
use Level23\Druid\Collections\OrderByCollection;

interface LimitInterface
{
    /**
     * Return the limit in array format so that it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void;

    /**
     * @param int $offset
     */
    public function setOffset(int $offset): void;

    /**
     * Get the limit which is currently configured.
     *
     * @return int|null
     */
    public function getLimit(): ?int;

    /**
     * Skip this many rows when returning results.
     *
     * @return int|null
     */
    public function getOffset(): ?int;

    /**
     * Return the fields which are used to sort the result by.
     *
     * @return \Level23\Druid\Collections\OrderByCollection
     */
    public function getOrderByCollection(): OrderByCollection;

    /**
     * Add an order by field to the collection.
     *
     * @param \Level23\Druid\OrderBy\OrderByInterface $orderBy
     */
    public function addOrderBy(OrderByInterface $orderBy): void;
}
