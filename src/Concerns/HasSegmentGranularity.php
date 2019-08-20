<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use InvalidArgumentException;
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
        if (is_string($segmentGranularity) && !Granularity::isValid($segmentGranularity)) {
            throw new InvalidArgumentException(
                'The given granularity is invalid: ' . $segmentGranularity . '. ' .
                'Allowed are: ' . implode(',', Granularity::values())
            );
        }

        $this->segmentGranularity = $segmentGranularity;

        return $this;
    }
}