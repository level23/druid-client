<?php
declare(strict_types=1);

namespace Level23\Druid\Dimensions;

class PrefixFilteredDimension implements DimensionInterface
{
    protected Dimension $dimension;

    protected string $prefix;

    /**
     * PrefixFilteredDimension constructor.
     *
     * @param Dimension $dimension
     * @param string    $prefix
     */
    public function __construct(Dimension $dimension, string $prefix)
    {
        $this->dimension = $dimension;
        $this->prefix    = $prefix;
    }

    /**
     * Return the dimension as it should be used in a druid query.
     *
     * @return array<string,string|array<mixed>>
     */
    public function toArray(): array
    {
        return [
            'type'     => 'prefixFiltered',
            'delegate' => $this->dimension->toArray(),
            'prefix'   => $this->prefix,
        ];
    }

    /**
     * Return the name of the dimension which is selected.
     *
     * @return string
     */
    public function getDimension(): string
    {
        return $this->dimension->getDimension();
    }

    /**
     * Return the output name of this dimension
     *
     * @return string
     */
    public function getOutputName(): string
    {
        return $this->dimension->getOutputName();
    }
}