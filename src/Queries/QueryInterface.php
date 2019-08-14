<?php
declare(strict_types=1);

namespace Level23\Druid\Queries;

use Level23\Druid\Context\ContextInterface;

interface QueryInterface
{
    /**
     * Return the query in array format so we can fire it to druid.
     *
     * @return array
     */
    public function getQuery(): array;

    /**
     * Set the query context
     *
     * @param \Level23\Druid\Context\ContextInterface $context
     */
    public function setContext(ContextInterface $context);

    /**
     * Return the query type. For example "groupBy" or "timeseries"
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Parse the response into something we can return to the user.
     *
     * @param array $response
     *
     * @return array
     */
    public function parseResponse(array $response): array;
}