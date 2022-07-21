<?php
declare(strict_types=1);

namespace Level23\Druid\Dimensions;

class ListFilteredDimension implements DimensionInterface
{
    protected Dimension $dimension;

    /**
     * @var string[]
     */
    protected array $values;

    protected bool $isWhitelist;

    /**
     * RegexFilteredDimension constructor.
     *
     * @param Dimension $dimension
     * @param string[]  $values
     * @param bool      $isWhitelist
     */
    public function __construct(Dimension $dimension, array $values, bool $isWhitelist = true)
    {
        $this->dimension   = $dimension;
        $this->values      = $values;
        $this->isWhitelist = $isWhitelist;
    }

    /**
     * Return the dimension as it should be used in a druid query.
     *
     * @return array<string,string|array<mixed>|bool>
     */
    public function toArray(): array
    {
        return [
            'type'        => 'listFiltered',
            'delegate'    => $this->dimension->toArray(),
            'values'      => $this->values,
            'isWhitelist' => $this->isWhitelist,
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