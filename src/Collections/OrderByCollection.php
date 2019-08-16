<?php
declare(strict_types=1);

namespace Level23\Druid\Collections;

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