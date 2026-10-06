<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class SqlQueryResponse extends QueryResponse
{
    /**
     * Return the rows returned by druid. The default druid SQL resultFormat is "object", which already returns rows
     * shaped as `[{column => value, ...}, ...]` — so `data()` and `raw()` are the same here.
     *
     * @return array<int,array<string,mixed>>
     */
    public function data(): array
    {
        /** @var array<int,array<string,mixed>> $rows */
        $rows = $this->response;

        return $rows;
    }
}
