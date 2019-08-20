<?php
declare(strict_types=1);

namespace Level23\Druid\Transforms;

interface TransformInterface
{
    /**
     * Return the transform in such a way so that we can use it in a druid query.
     *
     * @return mixed
     */
    public function toArray();
}