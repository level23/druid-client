<?php
declare(strict_types=1);

namespace Level23\Druid\TuningConfig;

interface TuningConfigInterface
{
    /**
     * Return the tuning config as it can be used in the druid query.
     *
     * @return array
     */
    public function toArray(): array;
}