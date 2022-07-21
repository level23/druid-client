<?php
declare(strict_types=1);

namespace Level23\Druid\Granularities;

use Level23\Druid\Types\Granularity;
use Level23\Druid\Collections\IntervalCollection;

abstract class AbstractGranularity
{
    protected string $queryGranularity;

    protected bool $rollup;

    protected IntervalCollection $intervals;

    /**
     * UniformGranularity constructor.
     *
     * @param string             $queryGranularity
     * @param bool               $rollup
     * @param IntervalCollection $intervals
     */
    public function __construct(string $queryGranularity, bool $rollup, IntervalCollection $intervals)
    {
        $this->queryGranularity = Granularity::validate($queryGranularity);
        $this->rollup           = $rollup;
        $this->intervals        = $intervals;
    }
}