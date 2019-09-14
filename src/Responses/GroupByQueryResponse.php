<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class GroupByQueryResponse extends QueryResponse
{
    public function __construct(array $response)
    {
        $this->rawResponse = $response;

        $this->response    = array_map(function ($row) {
            return $row['event'];
        }, $response);
    }
}