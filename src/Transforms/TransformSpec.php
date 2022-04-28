<?php
declare(strict_types=1);

namespace Level23\Druid\Transforms;

use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Collections\TransformCollection;

class TransformSpec
{
    protected TransformCollection $transforms;

    protected ?FilterInterface $filter;

    /**
     * TransformSpec constructor.
     *
     * @param \Level23\Druid\Collections\TransformCollection $transforms
     * @param FilterInterface|null                           $filter
     */
    public function __construct(TransformCollection $transforms, FilterInterface $filter = null)
    {
        $this->transforms = $transforms;
        $this->filter     = $filter;
    }

    /**
     * Return the transformSpec in such a way we can use it in a druid query.
     *
     * @return array<string,array<int,array<mixed>>|array<string,string|int|bool|array<mixed>>>
     */
    public function toArray(): array
    {
        $result = [];

        if ($this->transforms->count() > 0) {
            $result['transforms'] = $this->transforms->toArray();
        }

        if ($this->filter) {
            $result['filter'] = $this->filter->toArray();
        }

        return $result;
    }
}