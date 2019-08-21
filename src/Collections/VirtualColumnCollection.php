<?php
declare(strict_types=1);

namespace Level23\Druid\Collections;

use Level23\Druid\VirtualColumns\VirtualColumnInterface;

class VirtualColumnCollection extends BaseCollection
{
    public function __construct(VirtualColumnInterface ...$virtualColumns)
    {
        $this->items = $virtualColumns;
    }

    /**
     * Return an array representation of our items
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function (VirtualColumnInterface $item) {
            return $item->toArray();
        }, $this->items);
    }

    /**
     * We only accept objects of this type.
     *
     * @return string
     */
    public function getType(): string
    {
        return VirtualColumnInterface::class;
    }
}