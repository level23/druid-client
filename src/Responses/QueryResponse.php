<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

abstract class QueryResponse implements ResponseInterface
{
    /**
     * @var array<mixed>
     */
    protected array $response;

    /**
     * @param array<mixed> $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * Return the raw response as we have received it from druid.
     *
     * @return array<mixed>
     */
    public function raw(): array
    {
        return $this->response;
    }

    /**
     * Return the data in a "normalized" way, so we can easily iterate over it
     *
     * @return array<array<mixed>>
     */
    abstract public function data(): array;
}