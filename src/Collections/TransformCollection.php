<?php
declare(strict_types=1);

namespace Level23\Druid\Collections;

use Level23\Druid\Transforms\TransformInterface;

/**
 * @extends \Level23\Druid\Collections\BaseCollection<TransformInterface>
 */
class TransformCollection extends BaseCollection
{
    /**
     * DimensionCollection constructor.
     *
     * @param \Level23\Druid\Transforms\TransformInterface ...$transforms
     */
    public function __construct(TransformInterface ...$transforms)
    {
        $this->items = array_values($transforms);
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
     * @return array<int,array<mixed>>
     */
    public function toArray(): array
    {
        return array_map(fn(TransformInterface $item) => $item->toArray(), $this->items);
    }
}