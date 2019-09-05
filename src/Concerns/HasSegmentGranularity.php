<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Level23\Druid\Types\Granularity;

trait HasSegmentGranularity
{
    /**
     * @var null|string
     */
    protected $segmentGranularity;

    /**
     * @param string $segmentGranularity
     *
     * @return $this
     */
    public function segmentGranularity($segmentGranularity)
    {
        $this->segmentGranularity = Granularity::validate($segmentGranularity);

        return $this;
    }
}