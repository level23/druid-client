<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

abstract class QueryResponse implements ResponseInterface
{
    /**
     * @var array
     */
    protected $rawResponse;

    /**
     * @var array
     */
    protected $response;

    public function getRawResponse(): array
    {
        return $this->rawResponse;
    }

    public function getResponse(): array
    {
        return $this->response;
    }
}