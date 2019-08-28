<?php
declare(strict_types=1);

namespace Level23\Druid\Transforms;

use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Collections\TransformCollection;

class TransformSpec
{
    /**
     * @var TransformCollection
     */
    protected $transforms;

    /**
     * @var \Level23\Druid\Filters\FilterInterface|null
     */
    protected $filter;

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
     * @return array
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