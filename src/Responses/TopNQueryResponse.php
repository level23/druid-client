<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class TopNQueryResponse extends QueryResponse
{
    public function __construct(array $response)
    {
        $this->rawResponse = $response;

        if (isset($response[0])) {
            $this->response = array_map(function ($row) {
                return $row['result'];
            }, $response)[0];
        } else {
            $this->response = [];
        }
    }
}