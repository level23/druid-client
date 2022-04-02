<?php
declare(strict_types=1);

namespace Level23\Druid\Transforms;

class ExpressionTransform implements TransformInterface
{
    protected string $outputName;

    protected string $expression;

    /**
     * ExpressionTransform constructor.
     *
     * @param string $expression
     * @param string $outputName
     */
    public function __construct(string $expression, string $outputName)
    {
        $this->expression = $expression;
        $this->outputName = $outputName;
    }

    /**
     * Return the transform in such a way so that we can use it in a druid query.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'type'       => 'expression',
            'name'       => $this->outputName,
            'expression' => $this->expression,
        ];
    }
}