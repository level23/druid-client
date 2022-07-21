<?php
declare(strict_types=1);

namespace Level23\Druid\Collections;

use Level23\Druid\Dimensions\DimensionInterface;

/**
 * @extends \Level23\Druid\Collections\BaseCollection<DimensionInterface>
 */
class DimensionCollection extends BaseCollection
{
    /**
     * DimensionCollection constructor.
     *
     * @param \Level23\Druid\Dimensions\DimensionInterface ...$dimensions
     */
    public function __construct(DimensionInterface ...$dimensions)
    {
        $this->items = array_values($dimensions);
    }

    /**
     * We only accept objects of this type.
     *
     * @return string
     */
    public function getType(): string
    {
        return DimensionInterface::class;
    }

    /**
     * Return an array representation of our items
     *
     * @return array<array<string,string|array<mixed>>>
     */
    public function toArray(): array
    {
        return array_map(fn(DimensionInterface $item) => $item->toArray(), $this->items);
    }
}