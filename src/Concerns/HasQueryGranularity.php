<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Level23\Druid\Types\Granularity;

trait HasQueryGranularity
{
    /**
     * @var null|Granularity
     */
    protected ?Granularity $queryGranularity = null;

    /**
     * @param string|Granularity $queryGranularity
     *
     * @return $this
     */
    public function queryGranularity(string|Granularity $queryGranularity): self
    {
        $this->queryGranularity = is_string($queryGranularity) ? Granularity::from(strtolower($queryGranularity)) : $queryGranularity;

        return $this;
    }
}