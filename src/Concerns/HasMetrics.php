<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

trait HasMetrics
{
    /**
     * @var array
     */
    protected $metrics = [];

    /**
     * Give a list of metrics which you want to use in your SELECT query.
     *
     * @param array $metrics
     *
     * @return $this
     */
    public function metrics(array $metrics)
    {
        $this->metrics = $metrics;

        return $this;
    }
}