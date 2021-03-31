<?php
declare(strict_types=1);

namespace Level23\Druid\InputSources;

interface InputSourceInterface
{
    /**
     * Return the input source in a format so that we can send it to druid.
     *
     * @return array
     */
    public function toArray(): array;
}