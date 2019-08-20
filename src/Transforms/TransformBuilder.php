<?php
declare(strict_types=1);

namespace Level23\Druid;

use Level23\Druid\Concerns\HasFilter;
use Level23\Druid\Transforms\ExpressionTransform;

class TransformBuilder
{
    use HasFilter;

    /**
     * @var array|\Level23\Druid\Transforms\TransformInterface[]
     */
    protected $transforms = [];

    public function transform(string $as, string $expression)
    {
        $this->transforms[] = new ExpressionTransform($as, $expression);
    }

    /**
     * @return array|\Level23\Druid\Transforms\TransformInterface[]
     */
    public function getTransforms()
    {
        return $this->transforms;
    }
}