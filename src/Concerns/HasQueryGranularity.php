<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Level23\Druid\Types\Granularity;

trait HasQueryGranularity
{
    /**
     * @var null|\Level23\Druid\Types\Granularity|string
     */
    protected $queryGranularity;

    /**
     * @param \Level23\Druid\Types\Granularity|string $queryGranularity
     *
     * @return $this
     */
    public function queryGranularity($queryGranularity)
    {
        Granularity::validate($queryGranularity);

        $this->queryGranularity = $queryGranularity;

        return $this;
    }
}