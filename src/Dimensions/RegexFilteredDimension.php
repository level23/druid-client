<?php
declare(strict_types=1);

namespace Level23\Druid\Dimensions;

class RegexFilteredDimension implements DimensionInterface
{
    protected Dimension $dimension;

    protected string $regex;

    /**
     * RegexFilteredDimension constructor.
     *
     * @param Dimension $dimension
     * @param string    $regex
     */
    public function __construct(Dimension $dimension, string $regex)
    {
        $this->dimension = $dimension;
        $this->regex     = $regex;
    }

    /**
     * Return the dimension as it should be used in a druid query.
     *
     * @return array<string,string|array<mixed>>
     */
    public function toArray(): array
    {
        return [
            'type'     => 'regexFiltered',
            'delegate' => $this->dimension->toArray(),
            'pattern'  => $this->regex,
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