<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

abstract class QueryResponse implements ResponseInterface
{
    protected array $response;

    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * Return the raw response as we have received it from druid.
     *
     * @return array
     */
    public function raw(): array
    {
        return $this->response;
    }

    /**
     * Return the data in a "normalized" way, so we can easily iterate over it
     *
     * @return array
     */
    abstract public function data(): array;
}