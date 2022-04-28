<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class TopNQueryResponse extends QueryResponse
{
    /**
     * Return the data in a "normalized" way, so we can easily iterate over it
     *
     * @return array<array<mixed>>
     */
    public function data(): array
    {
        if (!isset($this->response[0])) {
            return [];
        }

        return array_map(function ($row) {
            /** @var array<string,array<array<mixed>>> $row */
            return $row['result'];
        }, $this->response)[0];
    }
}