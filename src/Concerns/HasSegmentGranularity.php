<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Level23\Druid\Types\Granularity;

trait HasSegmentGranularity
{
    /**
     * @var null|\Level23\Druid\Types\Granularity|string
     */
    protected $segmentGranularity;

    /**
     * @param \Level23\Druid\Types\Granularity|string $segmentGranularity
     *
     * @return $this
     */
    public function segmentGranularity($segmentGranularity)
    {
        Granularity::validate($segmentGranularity);

        $this->segmentGranularity = $segmentGranularity;

        return $this;
    }
}