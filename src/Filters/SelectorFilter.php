<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

class SelectorFilter implements FilterInterface
{
    protected string $dimension;

    protected ?string $value;

    /**
     * InFilter constructor.
     *
     * @param string      $dimension
     * @param string|null $value
     */
    public function __construct(string $dimension, ?string $value)
    {
        $this->value     = $value;
        $this->dimension = $dimension;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|null|array<string,string|int|bool|array<mixed>>>
     */
    public function toArray(): array
    {
        return [
            'type'      => 'selector',
            'dimension' => $this->dimension,
            'value'     => $this->value,
        ];
    }
}