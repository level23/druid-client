<?php
declare(strict_types=1);

namespace Level23\Druid\Context;

class TimeSeriesQueryContext extends QueryContext implements ContextInterface
{
    /**
     * Disable timeseries zero-filling behavior, so only buckets with results will be returned.
     *
     * Default: false
     *
     * @param bool $skipEmptyBuckets
     *
     * @return $this
     */
    public function setSkipEmptyBuckets(bool $skipEmptyBuckets): self
    {
        $this->properties['skipEmptyBuckets'] = $skipEmptyBuckets;

        return $this;
    }
}