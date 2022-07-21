<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

/**
 * Class SpatialRadiusFilter
 *
 * @package Level23\Druid\Filters
 */
class SpatialRadiusFilter implements FilterInterface
{
    protected string $dimension;

    /**
     * @var float[]
     */
    protected array $coords;

    protected float $radius;

    /**
     * SpatialRadiusFilter constructor.
     *
     * @param string  $dimension The dimension to filter on
     * @param float[] $coords    Origin coordinates in the form [x, y, z, …]
     * @param float   $radius    The float radius value
     */
    public function __construct(
        string $dimension,
        array $coords,
        float $radius
    ) {
        $this->dimension = $dimension;
        $this->coords    = $coords;
        $this->radius    = $radius;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|array<string,string|float|float[]>>
     */
    public function toArray(): array
    {
        return [
            'type'      => 'spatial',
            'dimension' => $this->dimension,
            'bound'     => [
                'type'   => 'radius',
                'coords' => $this->coords,
                'radius' => $this->radius,
            ],
        ];
    }
}