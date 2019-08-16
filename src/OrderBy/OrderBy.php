<?php
declare(strict_types=1);

namespace Level23\Druid\OrderBy;

use InvalidArgumentException;
use Level23\Druid\Types\OrderByDirection;
use Level23\Druid\Types\SortingOrder;

class OrderBy implements OrderByInterface
{
    /**
     * @var string
     */
    protected $dimension;

    /**
     * @var \Level23\Druid\Types\OrderByDirection|string
     */
    protected $direction;

    /**
     * @var \Level23\Druid\Types\SortingOrder|string
     */
    protected $dimensionOrder;

    /**
     * OrderBy constructor.
     *
     * @param string                  $dimension
     * @param string|OrderByDirection $direction
     * @param string|SortingOrder     $dimensionOrder
     */
    public function __construct(string $dimension, $direction = 'ascending', $dimensionOrder = 'lexicographic')
    {
        if (is_string($direction) && !OrderByDirection::isValid($direction)) {
            throw new InvalidArgumentException(
                'Invalid order by direction given: ' . $direction .
                '. Valid options are: ' . implode(', ', OrderByDirection::values())
            );
        }

        if (is_string($dimensionOrder) && !SortingOrder::isValid($dimensionOrder)) {
            throw new InvalidArgumentException(
                'Invalid dimension order given: ' . $dimensionOrder .
                '. Valid options are: ' . implode(', ', SortingOrder::values())
            );
        }

        $this->dimension      = $dimension;
        $this->direction      = $direction;
        $this->dimensionOrder = $dimensionOrder;
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
     * @return \Level23\Druid\Types\OrderByDirection
     */
    public function getDirection(): OrderByDirection
    {
        return ($this->direction == "ascending" ? OrderByDirection::ASC() : OrderByDirection::DESC());
    }
}
