<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class SegmentMetadataQueryResponse extends QueryResponse
{
    public function __construct(array $response)
    {
        $this->rawResponse = $response;

        $columns = [];
        if (isset($response[0]['columns'])) {
            array_walk($response[0]['columns'], function ($value, $key) use (&$columns) {
                $columns[] = array_merge($value, ['field' => $key]);
            });
        }

        $this->response = $columns;
    }
}