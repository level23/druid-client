<?php
declare(strict_types=1);

namespace Level23\Druid\VirtualColumns;

use Level23\Druid\Types\DataType;

class VirtualColumn implements VirtualColumnInterface
{
    protected string $name;

    protected string $expression;

    protected DataType $outputType;

    /**
     * VirtualColumn constructor.
     *
     * @param string $expression An druid expression
     * @param string $as
     * @param string|DataType $outputType
     *
     * @see https://druid.apache.org/docs/latest/misc/math-expr.html
     */
    public function __construct(string $expression, string $as, string|DataType $outputType = DataType::FLOAT)
    {
        $this->name       = $as;
        $this->expression = $expression;
        $this->outputType = is_string($outputType) ? DataType::from(strtolower($outputType)) : $outputType;
    }

    /**
     * Return the virtual column as it can be used in a druid query.
     *
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'type'       => 'expression',
            'name'       => $this->name,
            'expression' => $this->expression,
            'outputType' => $this->outputType->value,
        ];
    }
}
