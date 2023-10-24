<?php

declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\Filters\FilterInterface;

class SelectorFilterIsNotNull implements FilterInterface
{
    public function __construct(
        protected string $dimension
    ) {
    }

    public function toArray(): array
    {
        $result = [
            "type" => "not",
            "field" => [
                'type'      => 'selector',
                'dimension' => $this->dimension,
                'value'     => null
            ]
        ];

        return $result;
    }
}
