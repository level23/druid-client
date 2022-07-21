<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Level23\Druid\Types\Granularity;

trait HasQueryGranularity
{
    /**
     * @var null|string
     */
    protected ?string $queryGranularity = null;

    /**
     * @param string $queryGranularity
     *
     * @return $this
     */
    public function queryGranularity(string $queryGranularity): self
    {
        $this->queryGranularity = Granularity::validate($queryGranularity);

        return $this;
    }
}