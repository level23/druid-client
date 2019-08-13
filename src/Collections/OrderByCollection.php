<?php
declare(strict_types=1);

namespace Level23\Druid\Collections;

use Level23\Druid\OrderBy\OrderBy;
use Level23\Druid\OrderBy\OrderByInterface;

class OrderByCollection extends BaseCollection
{
    /**
     * OrderByCollection constructor.
     *
     * @param \Level23\Druid\OrderBy\OrderByInterface ...$orderByColumns
     */
    public function __construct(OrderByInterface ...$orderByColumns)
    {
        $this->items = $orderByColumns;
    }

    /**
     * @param \Level23\Druid\OrderBy\OrderByInterface $orderBy
     */
    public function add(OrderByInterface $orderBy)
    {
        $this->items[] = $orderBy;
    }

    /**
     * Make a dimension collection of the given array.
     * For associative array's, we expect the key to be the "dimension" and the value the "output name".
     *
     * @param array $orderByColumns
     *
     * @return \Level23\Druid\Collections\OrderByCollection
     */
    public static function make(array $orderByColumns): OrderByCollection
    {
        $objects = [];

        foreach ($orderByColumns as $key => $value) {
            if ($value instanceof OrderBy) {
                $objects[] = $value;
            } elseif (is_numeric($key)) {
                $objects[] = new OrderBy($value);
            } else {
                $objects[] = new OrderBy($key, $value);
            }
        }

        return new OrderByCollection(...$objects);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->items as $orderBy) {
            $result[] = $orderBy->getOrderBy();
        }

        return $result;
    }

    /**
     * We only accept objects of this type.
     *
     * @return string
     */
    public function getType(): string
    {
        return OrderByInterface::class;
    }
}