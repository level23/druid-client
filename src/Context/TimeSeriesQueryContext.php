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
     * @var bool
     */
    public $skipEmptyBuckets;

    /**
     * Return the context as it can be used in the druid query.
     *
     * @return array
     */
    public function getContext(): array
    {
        $result = parent::getContext();

        if ($this->skipEmptyBuckets !== null) {
            $result['skipEmptyBuckets'] = $this->skipEmptyBuckets;
        }

        return $result;
    }
}