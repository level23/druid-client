<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use InvalidArgumentException;
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
        if (is_string($queryGranularity) && !Granularity::isValid($queryGranularity)) {
            throw new InvalidArgumentException(
                'The given granularity is invalid: ' . $queryGranularity . '. ' .
                'Allowed are: ' . implode(',', Granularity::values())
            );
        }

        $this->queryGranularity = $queryGranularity;

        return $this;
    }
}