<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

use Level23\Druid\PostAggregations\PostAggregatorInterface;

class ExpressionPostAggregator implements PostAggregatorInterface
{
    protected string $outputName;

    protected string $expression;

    public function __construct(
        string $outputName,
        string $expression
    ) {
        $this->outputName   = $outputName;
        $this->expression   = $expression;
    }

    public function toArray(): array
    {
        return [
            'type'          => 'expression',
            'name'          => $this->outputName,
            'expression'    => $this->expression,
        ];
    }
}
