<?php
declare(strict_types=1);

namespace Level23\Druid\HavingFilters;

class DimensionSelectorHavingFilter implements HavingFilterInterface
{
    protected string $dimension;

    protected string $value;

    /**
     * LessThanHavingFilter constructor.
     *
     * @param string $dimension
     * @param string $value
     */
    public function __construct(string $dimension, string $value)
    {
        $this->dimension = $dimension;
        $this->value     = $value;
    }

    /**
     * Return the having filter as it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'      => 'dimSelector',
            'dimension' => $this->dimension,
            'value'     => $this->value,
        ];
    }
}