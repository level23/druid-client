<?php
declare(strict_types=1);

namespace Level23\Druid\Firehoses;

/**
 * Interface FirehoseInterface
 *
 * @package Level23\Druid\Firehoses
 * @deprecated
 */
interface FirehoseInterface
{
    /**
     * Return the firehose in a format so that we can send it to druid.
     *
     * @return array
     */
    public function toArray(): array;
}