<?php
declare(strict_types=1);

namespace Level23\Druid\Transforms;

class ExpressionTransform implements TransformInterface
{
    /**
     * @var string
     */
    protected $outputName;

    /**
     * @var string
     */
    protected $expression;

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
     * @return mixed
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