<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Level23\Druid\Types\Granularity;

trait HasSegmentGranularity
{
    /**
     * @var null|Granularity
     */
    protected ?Granularity $segmentGranularity = null;

    /**
     * @param string|Granularity $segmentGranularity
     *
     * @return $this
     */
    public function segmentGranularity(string|Granularity $segmentGranularity): self
    {
        $this->segmentGranularity = is_string($segmentGranularity) ? Granularity::from(strtolower($segmentGranularity)) : $segmentGranularity;

        return $this;
    }
}