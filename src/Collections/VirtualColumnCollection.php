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

    public function toArray(): array
    {
        $result = [];

        foreach ($this->items as $virtualColumn) {
            $result[] = $virtualColumn->getVirtualColumn();
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
        return VirtualColumnInterface::class;
    }
}