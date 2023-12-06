<?php
declare(strict_types=1);

namespace Level23\Druid\PostAggregations;

use Level23\Druid\Types\DataType;

class ExpressionPostAggregator implements PostAggregatorInterface
{
    protected string $outputName;

    protected string $expression;

    protected ?string $ordering;

    protected DataType|string|null $outputType;

    public function __construct(
        string $outputName,
        string $expression,
        ?string $ordering = null,
        DataType|string|null $outputType = null

    ) {
        $this->outputName = $outputName;
        $this->expression = $expression;
        $this->ordering   = $ordering;
        $this->outputType = $outputType;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array<string,string>
     */
    public function toArray(): array
    {
        $result = [
            'type'       => 'expression',
            'name'       => $this->outputName,
            'expression' => $this->expression,
        ];

        if ($this->ordering) {
            $result['ordering'] = $this->ordering;
        }

        if ($this->outputType) {
            if ($this->outputType instanceof DataType) {
                $result['outputType'] = $this->outputType->value;
            } else {
                $result['outputType'] = $this->outputType;
            }
        }

        return $result;
    }
}