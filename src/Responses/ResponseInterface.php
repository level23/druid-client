<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

interface ResponseInterface
{
    /**
     * Return the raw response as we have received it from druid.
     *
     * @return array<mixed>
     */
    public function raw(): array;

    /**
     * Return the data in a "normalized" way, so we can easily iterate over it
     *
     * @return array<array<mixed>>
     */
    public function data(): array;
}
