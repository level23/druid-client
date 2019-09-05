<?php
declare(strict_types=1);

namespace Level23\Druid\Granularities;

use Level23\Druid\Types\Granularity;
use Level23\Druid\Collections\IntervalCollection;

class UniformGranularity extends AbstractGranularity implements GranularityInterface
{
    /**
     * @var string
     */
    protected $segmentGranularity;

    /**
     * UniformGranularity constructor.
     *
     * @param string             $segmentGranularity
     * @param string             $queryGranularity
     * @param bool               $rollup
     * @param IntervalCollection $intervals
     */
    public function __construct(
        string $segmentGranularity,
        string $queryGranularity,
        bool $rollup,
        IntervalCollection $intervals
    ) {
        parent::__construct($queryGranularity, $rollup, $intervals);

        $this->segmentGranularity = Granularity::validate($segmentGranularity);
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