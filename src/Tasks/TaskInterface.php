<?php
declare(strict_types=1);

namespace Level23\Druid\Tasks;

interface TaskInterface
{
    /**
     * Return the task in a format so that we can send it to druid.
     *
     * @return array<string,mixed>
     */
    public function toArray(): array;
}