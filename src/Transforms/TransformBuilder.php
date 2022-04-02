<?php
declare(strict_types=1);

namespace Level23\Druid\Transforms;

use Level23\Druid\Concerns\HasFilter;

class TransformBuilder
{
    use HasFilter;

    /**
     * @var array|\Level23\Druid\Transforms\TransformInterface[]
     */
    protected array $transforms = [];

    /**
     * Build a new transform.
     *
     * For all the available options in expressions, see this link:
     *
     * @see https://druid.apache.org/docs/latest/misc/math-expr.html
     *
     * @param string $expression
     * @param string $as
     *
     * @return $this
     */
    public function transform(string $expression, string $as)
    {
        $this->transforms[] = new ExpressionTransform($expression, $as);

        return $this;
    }

    /**
     * @return array|\Level23\Druid\Transforms\TransformInterface[]
     */
    public function getTransforms()
    {
        return $this->transforms;
    }
}