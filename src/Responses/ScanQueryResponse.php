<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class ScanQueryResponse extends QueryResponse
{
    /**
     * Return the data in a "normalized" way, so we can easily iterate over it
     *
     * @return array<int,string|int|array<mixed>>
     */
    public function data(): array
    {
        $result = [];
        array_walk($this->response, function ($row) use (&$result) {
            /** @var array<string,array<string|int|array<mixed>>> $row */
            array_push($result, ...$row['events']);
        });

        return $result;
    }
}