<?php
declare(strict_types=1);

namespace Level23\Druid\Collections;

use Level23\Druid\Dimensions\DimensionInterface;

class DimensionCollection extends BaseCollection
{
    /**
     * DimensionCollection constructor.
     *
     * @param \Level23\Druid\Dimensions\DimensionInterface ...$dimensions
     */
    public function __construct(DimensionInterface ...$dimensions)
    {
        $this->items = $dimensions;
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

    public function toArray(): array
    {
        $result = [];

        foreach ($this->items as $dimension) {
            $result[] = $dimension->getDimensionForQuery();
        }

        return $result;
    }
}