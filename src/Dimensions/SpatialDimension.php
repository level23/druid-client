<?php
declare(strict_types=1);

namespace Level23\Druid\Dimensions;

class SpatialDimension
{
    protected string $dimension;

    protected array $dims;

    /**
     * @param string $dimension The name of the spatial dimension. A spatial dimension may be constructed from multiple
     *                          other dimensions or it may already exist as part of an event. If a spatial dimension
     *                          already exists, it must be an array of coordinate values.
     * @param array  $dims      A list of dimension names that comprise a spatial dimension.
     */
    public function __construct(string $dimension, array $dims)
    {

        $this->dimension = $dimension;
        $this->dims      = $dims;
    }

    public function toArray(): array
    {
        return [
            'dimName' => $this->dimension,
            'dims'    => $this->dims,
        ];
    }
}
