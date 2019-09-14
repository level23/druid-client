<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class ScanQueryResponse extends QueryResponse
{
    public function __construct(array $response)
    {
        $this->rawResponse = $response;

        $result = [];
        array_walk($response, function ($row) use (&$result) {
            array_push($result, ...$row['events']);
        });

        $this->response = $result;
    }
}