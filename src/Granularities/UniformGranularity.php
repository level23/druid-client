<?php
declare(strict_types=1);

namespace Level23\Druid\Granularities;

use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Types\Granularity;

class UniformGranularity implements GranularityInterface
{
    /**
     * @var \Level23\Druid\Types\Granularity|string
     */
    protected $segmentGranularity;

    /**
     * @var \Level23\Druid\Types\Granularity|string
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
     * @param string|Granularity $segmentGranularity
     * @param string|Granularity $queryGranularity
     * @param bool               $rollup
     * @param IntervalCollection $intervals
     */
    public function __construct($segmentGranularity, $queryGranularity, bool $rollup, IntervalCollection $intervals)
    {
        $this->segmentGranularity = $segmentGranularity;
        $this->queryGranularity   = $queryGranularity;
        $this->rollup             = $rollup;
        $this->intervals          = $intervals;
    }

    /**
     * Return the granularity in array format so that we can use it in a druid request.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'               => 'uniform',
            'segmentGranularity' => $this->segmentGranularity,
            'queryGranularity'   => $this->queryGranularity,
            'rollup'             => $this->rollup,
            'intervals'          => $this->intervals->toArray(),
        ];
    }
}