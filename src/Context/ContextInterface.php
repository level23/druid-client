<?php
declare(strict_types=1);

namespace Level23\Druid\Context;

interface ContextInterface
{
    /**
     * Return the context as it can be used in the druid query.
     *
     * @return array
     */
    public function toArray(): array;
}