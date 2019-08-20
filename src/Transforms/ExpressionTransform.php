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
     * @param string $outputName
     * @param string $expression
     */
    public function __construct(string $outputName, string $expression)
    {
        $this->outputName = $outputName;
        $this->expression = $expression;
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