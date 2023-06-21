<?php
declare(strict_types=1);

namespace Level23\Druid\OrderBy;

use Level23\Druid\Types\SortingOrder;
use Level23\Druid\Types\OrderByDirection;

class OrderBy implements OrderByInterface
{
    protected string $dimension;

    protected OrderByDirection $direction;

    protected SortingOrder $dimensionOrder;

    /**
     * OrderBy constructor.
     *
     * @param string                  $dimension
     * @param string|OrderByDirection $direction
     * @param string|SortingOrder     $dimensionOrder
     */
    public function __construct(
        string $dimension,
        string|OrderByDirection $direction = OrderByDirection::ASC,
        string|SortingOrder $dimensionOrder = SortingOrder::LEXICOGRAPHIC
    ) {
        $this->dimension      = $dimension;
        $this->direction      = is_string($direction) ? OrderByDirection::make($direction) : $direction;
        $this->dimensionOrder = is_string($dimensionOrder) ? SortingOrder::from($dimensionOrder) : $dimensionOrder;
    }

    /**
     * Return the order by in array format so that it can be used in a druid query.
     *
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'dimension'      => $this->dimension,
            'direction'      => $this->direction->value,
            'dimensionOrder' => $this->dimensionOrder->value,
        ];
    }

    /**
     * The dimension where we should order on.
     *
     * @return string
     */
    public function getDimension(): string
    {
        return $this->dimension;
    }

    /**
     * Return the direction of the order by
     *
     * @return OrderByDirection
     */
    public function getDirection(): OrderByDirection
    {
        return $this->direction;
    }
}
