<?php
declare(strict_types=1);

namespace Level23\Druid\OrderBy;

use Level23\Druid\Types\OrderByDirection;

interface OrderByInterface
{
    /**
     * Return the order by in array format so that it can be used in a druid query.
     *
     * @return array
     */
    public function getOrderBy(): array;

    /**
     * The dimension where we should order on.
     * @return string
     */
    public function getDimension(): string;

    /**
     * Return the direction of the order by
     * @return \Level23\Druid\Types\OrderByDirection
     */
    public function getDirection(): OrderByDirection;
}
