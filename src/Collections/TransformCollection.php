<?php
declare(strict_types=1);

namespace Level23\Druid\Collections;

use Level23\Druid\Transforms\TransformInterface;

class TransformCollection extends BaseCollection
{
    /**
     * DimensionCollection constructor.
     *
     * @param \Level23\Druid\Transforms\TransformInterface[] $transforms
     */
    public function __construct(TransformInterface ...$transforms)
    {
        $this->items = $transforms;
    }

    /**
     * We only accept objects of this type.
     *
     * @return string
     */
    public function getType(): string
    {
        return TransformInterface::class;
    }

    /**
     * Return an array representation of our items
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function (TransformInterface $item) {
            return $item->toArray();
        }, $this->items);
    }
}