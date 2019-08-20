<?php
declare(strict_types=1);

namespace Level23\Druid\Granularities;

use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Types\Granularity;

class ArbitraryGranularity implements GranularityInterface
{
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
     * @param string|Granularity $queryGranularity
     * @param bool               $rollup
     * @param IntervalCollection $intervals
     */
    public function __construct($queryGranularity, bool $rollup, IntervalCollection $intervals)
    {
        $this->queryGranularity = $queryGranularity;
        $this->rollup           = $rollup;
        $this->intervals        = $intervals;
    }

    /**
     * Return the granularity in array format so that we can use it in a druid request.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'             => 'arbitrary',
            'queryGranularity' => $this->queryGranularity,
            'rollup'           => $this->rollup,
            'intervals'        => $this->intervals->toArray(),
        ];
    }
}