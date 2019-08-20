<?php
declare(strict_types=1);

namespace Level23\Druid\Granularities;

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
     * @var array|\Level23\Druid\Interval\IntervalInterface[]
     */
    protected $intervals;

    /**
     * UniformGranularity constructor.
     *
     * @param string|\Level23\Druid\Types\Granularity $queryGranularity
     * @param bool                                    $rollup
     * @param array                                   $intervals
     */
    public function __construct($queryGranularity, bool $rollup, array $intervals)
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
        return [];
    }
}