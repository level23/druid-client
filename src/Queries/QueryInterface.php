<?php
declare(strict_types=1);

namespace Level23\Druid\Queries;

interface QueryInterface
{
    /**
     * Return the query in array format so we can fire it to druid.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Parse the response into something we can return to the user.
     *
     * @param array $response
     *
     * @return array
     */
    public function parseResponse(array $response): array;
}