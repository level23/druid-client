<?php

namespace Level23\Druid\Filters;

class InFilter implements FilterInterface
{
    /**
     * @var string
     */
    protected $dimension;

    /**
     * @var array
     */
    protected $values;

    /**
     * InFilter constructor.
     *
     * @param string $dimension
     * @param array  $values
     */
    public function __construct(string $dimension, array $values)
    {
        $this->values    = $values;
        $this->dimension = $dimension;
    }

    /**
     * Return the filter as it can be used in the druid query.
     * @return array
     */
    public function getFilter(): array
    {
        return [
            'type'      => 'in',
            'dimension' => $this->dimension,
            'values'    => $this->values,
        ];
    }
}