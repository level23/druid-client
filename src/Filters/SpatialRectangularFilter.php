<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

/**
 * Class SpatialRectangularFilter
 *
 * @package Level23\Druid\Filters
 */
class SpatialRectangularFilter implements FilterInterface
{
    protected string $dimension;

    protected array $minCoords;

    protected array $maxCoords;

    /**
     * SpatialRectangularFilter constructor.
     *
     * @param string $dimension The dimension to filter on
     * @param array  $minCoords List of minimum dimension coordinates for coordinates [x, y, z, …]
     * @param array  $maxCoords List of maximum dimension coordinates for coordinates [x, y, z, …]
     */
    public function __construct(
        string $dimension,
        array $minCoords,
        array $maxCoords
    ) {
        $this->dimension = $dimension;
        $this->minCoords = $minCoords;
        $this->maxCoords = $maxCoords;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'      => 'spatial',
            'dimension' => $this->dimension,
            'bound'     => [
                'type'      => 'rectangular',
                'minCoords' => $this->minCoords,
                'maxCoords' => $this->maxCoords,
            ],
        ];
    }
}