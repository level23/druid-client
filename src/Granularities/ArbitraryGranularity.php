<?php
declare(strict_types=1);

namespace Level23\Druid\Granularities;

use InvalidArgumentException;
use Level23\Druid\Types\Granularity;
use Level23\Druid\Collections\IntervalCollection;

class ArbitraryGranularity extends AbstractGranularity implements GranularityInterface
{
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