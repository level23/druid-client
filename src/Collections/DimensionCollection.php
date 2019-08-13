<?php
declare(strict_types=1);

namespace Level23\Druid\Collections;

use Level23\Druid\Dimensions\Dimension;
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

    /**
     * Add one or more dimensions from an array.
     *
     * @param array $dimensions
     */
    public function addFromArray(array $dimensions)
    {
        foreach ($dimensions as $key => $value) {
            if ($value instanceof DimensionInterface) {
                $this->items[] = $value;
            } elseif (is_numeric($key)) {
                $this->items[] = new Dimension($value);
            } else {
                $this->items[] = new Dimension($key, $value);
            }
        }
    }

    /**
     * @param \Level23\Druid\Dimensions\DimensionInterface $dimension
     */
    public function add(DimensionInterface $dimension)
    {
        $this->items[] = $dimension;
    }

    /**
     * Make a dimension collection of the given array.
     * For associative array's, we expect the key to be the "dimension" and the value the "output name".
     *
     * @param array $dimensions
     *
     * @return \Level23\Druid\Collections\DimensionCollection
     */
    public static function make(array $dimensions): DimensionCollection
    {
        $obj = new DimensionCollection();
        $obj->addFromArray($dimensions);

        return $obj;
    }

    public function toArray(): array
    {
        $result = [];

        foreach ($this->items as $dimension) {
            $result[] = $dimension->getDimension();
        }

        return $result;
    }
}