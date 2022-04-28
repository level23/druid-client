<?php
declare(strict_types=1);

namespace Level23\Druid\Collections;

use Level23\Druid\OrderBy\OrderByInterface;

/**
 * @extends \Level23\Druid\Collections\BaseCollection<OrderByInterface>
 */
class OrderByCollection extends BaseCollection
{
    /**
     * OrderByCollection constructor.
     *
     * @param \Level23\Druid\OrderBy\OrderByInterface ...$orderByColumns
     */
    public function __construct(OrderByInterface ...$orderByColumns)
    {
        $this->items = array_values($orderByColumns);
    }

    /**
     * Return an array representation of our items
     *
     * @return array<array<string,string>>
     */
    public function toArray(): array
    {
        return array_map(fn(OrderByInterface $item) => $item->toArray(), $this->items);
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