<?php
declare(strict_types=1);

namespace Level23\Druid\Lookups;

class MapLookup implements LookupInterface
{
    /**
     * @param array<int|string|float,int|string|float> $map
     */
    public function __construct(protected array $map)
    {

    }

    public function toArray(): array
    {
        return [
            'type' => 'map',
            'map'  => $this->map,
        ];
    }
}