<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

/**
 * Class SpatialPolygonFilter
 *
 * @package Level23\Druid\Filters
 */
class SpatialPolygonFilter implements FilterInterface
{
    protected string $dimension;

    /**
     * @var float[]
     */
    protected array $abscissa;

    /**
     * @var float[]
     */
    protected array $ordinate;

    /**
     * SpatialPolygonFilter constructor.
     *
     * @param string  $dimension The dimension to filter on
     * @param float[] $abscissa  Horizontal coordinate for corners of the polygon
     * @param float[] $ordinate  Vertical coordinate for corners of the polygon
     */
    public function __construct(
        string $dimension,
        array $abscissa,
        array $ordinate
    ) {
        $this->dimension = $dimension;
        $this->abscissa  = $abscissa;
        $this->ordinate  = $ordinate;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|array<string,string|float[]>>
     */
    public function toArray(): array
    {
        return [
            'type'      => 'spatial',
            'dimension' => $this->dimension,
            'bound'     => [
                'type'     => 'polygon',
                'abscissa' => $this->abscissa,
                'ordinate' => $this->ordinate,
            ],
        ];
    }
}