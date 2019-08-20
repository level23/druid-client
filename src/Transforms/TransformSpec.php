<?php
declare(strict_types=1);

namespace Level23\Druid\Transforms;

use Level23\Druid\Filters\FilterInterface;

class TransformSpec
{
    /**
     * @var array|TransformInterface[]
     */
    protected $transforms;

    /**
     * @var \Level23\Druid\Filters\FilterInterface|null
     */
    protected $filter;

    /**
     * TransformSpec constructor.
     *
     * @param array|TransformInterface[] $transforms
     * @param FilterInterface|null       $filter
     */
    public function __construct(array $transforms, FilterInterface $filter = null)
    {
        $this->transforms = $transforms;
        $this->filter     = $filter;
    }

    /**
     * Return the transformSpec in such a way we can use it in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        $transforms = [];

        foreach ($this->transforms as $transform) {
            $transforms[] = $transform->toArray();
        }

        $result = [];

        if ($this->transforms) {
            $result['transforms'] = $transforms;
        }

        if ($this->filter) {
            $result['filter'] = $this->filter->toArray();
        }

        return $result;
    }
}