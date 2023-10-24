<?php

declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\Filters\FilterInterface;

class SelectorFilterIsNull implements FilterInterface
{
    public function __construct(
        protected string $dimension
    ) {
    }

    public function toArray(): array
    {
        $result = [
            'type'      => 'selector',
            'dimension' => $this->dimension,
            'value'     => null
        ];

        return $result;
    }
}
