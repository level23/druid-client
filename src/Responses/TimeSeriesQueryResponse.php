<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class TimeSeriesQueryResponse extends QueryResponse
{
    public function __construct(array $response, string $timeOutputName)
    {
        $this->rawResponse = $response;

        $this->response = array_map(function ($row) use ($timeOutputName) {
            $row['result'][$timeOutputName] = $row['timestamp'];

            return $row['result'];
        }, $response);
    }
}