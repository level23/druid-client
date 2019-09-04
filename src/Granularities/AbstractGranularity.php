<?php
declare(strict_types=1);

namespace Level23\Druid\Granularities;

use InvalidArgumentException;
use Level23\Druid\Types\Granularity;
use Level23\Druid\Collections\IntervalCollection;

abstract class AbstractGranularity
{
    /**
     * @var string
     */
    protected $queryGranularity;

    /**
     * @var bool
     */
    protected $rollup;

    /**
     * @var IntervalCollection
     */
    protected $intervals;

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