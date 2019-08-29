<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Level23\Druid\TuningConfig\TuningConfig;

trait HasTuningConfig
{
    /**
     * @var TuningConfig|null
     */
    protected $tuningConfig;

    /**
     * Set the tuning config.
     *
     * @param array|TuningConfig $tuningConfig
     *
     * @return $this
     */
    public function tuningConfig($tuningConfig)
    {
        if (!$tuningConfig instanceof TuningConfig) {
            $tuningConfig = new TuningConfig($tuningConfig);
        }

        $this->tuningConfig = $tuningConfig;

        return $this;
    }
}