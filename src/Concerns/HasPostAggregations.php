<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

trait HasPostAggregations
{
    /**
     * @var array|\Level23\Druid\PostAggregations\PostAggregatorInterface[]
     */
    protected $postAggregations = [];
}