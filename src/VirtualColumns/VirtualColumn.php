<?php
declare(strict_types=1);

namespace Level23\Druid\VirtualColumns;

use InvalidArgumentException;
use Level23\Druid\Types\DataType;

class VirtualColumn implements VirtualColumnInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $expression;

    /**
     * @var \Level23\Druid\Types\DataType|string
     */
    protected $outputType;

    /**
     * VirtualColumn constructor.
     *
     * @param string                               $expression An druid expression
     * @param string                               $as
     * @param string|\Level23\Druid\Types\DataType $outputType
     *
     * @see https://druid.apache.org/docs/latest/misc/math-expr.html
     */
    public function __construct(string $expression, string $as, $outputType = 'float')
    {
        if (is_string($outputType) && !DataType::isValid($outputType)) {
            throw new InvalidArgumentException(
                'The given output type is invalid: ' . $outputType . '. ' .
                'Valid values are: ' . implode(', ', DataType::values())
            );
        }

        $this->name       = $as;
        $this->expression = $expression;
        $this->outputType = $outputType;
    }

    /**
     * Return the virtual column as it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'       => 'expression',
            'name'       => $this->name,
            'expression' => $this->expression,
            'outputType' => $this->outputType,
        ];
    }
}
