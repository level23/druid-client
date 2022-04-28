<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class SearchQueryResponse extends QueryResponse
{
    /**
     * Return the data in a "normalized" way, so we can easily iterate over it
     *
     * @return array<mixed>
     */
    public function data(): array
    {
        return array_map(function ($row) {
            /** @var array<string,array<mixed>> $row */
            return $row['result'];
        }, $this->response)[0];
    }
}