<?php
declare(strict_types=1);

namespace Level23\Druid\OrderBy;

use Level23\Druid\Types\SortingOrder;
use Level23\Druid\Types\OrderByDirection;

class OrderBy implements OrderByInterface
{
    /**
     * @var string
     */
    protected $dimension;

    /**
     * @var string
     */
    protected $direction;

    /**
     * @var string
     */
    protected $dimensionOrder;

    /**
     * OrderBy constructor.
     *
     * @param string                  $dimension
     * @param string $direction
     * @param string     $dimensionOrder
     */
    public function __construct(string $dimension, string $direction = 'ascending', string $dimensionOrder = 'lexicographic')
    {
        $this->dimension      = $dimension;
        $this->direction      = OrderByDirection::validate($direction);
        $this->dimensionOrder = SortingOrder::validate($dimensionOrder);
    }

    /**
     * Return the order by in array format so that it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'dimension'      => $this->dimension,
            'direction'      => $this->direction,
            'dimensionOrder' => $this->dimensionOrder,
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
     * @return string
     */
    public function getDirection(): string
    {
        return ($this->direction == 'ascending' ? OrderByDirection::ASC : OrderByDirection::DESC);
    }
}
