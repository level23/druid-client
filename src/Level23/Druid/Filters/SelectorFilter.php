<?php

namespace Level23\Druid\Filters;

class SelectorFilter implements FilterInterface
{
    /**
     * @var string
     */
    protected $dimension;

    /**
     * @var string
     */
    protected $value;

    /**
     * InFilter constructor.
     *
     * @param string $dimension
     * @param string $value
     */
    public function __construct(string $dimension, string $value)
    {
        $this->value     = $value;
        $this->dimension = $dimension;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array
     */
    public function getFilter(): array
    {
        return [
            'type'      => 'selector',
            'dimension' => $this->dimension,
            'value'     => $this->value,
        ];
    }
}