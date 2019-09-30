<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class GroupByQueryResponse extends QueryResponse
{
    /**
     * Return the data in a "normalized" way so we can easily iterate over it
     *
     * @return array
     */
    public function data(): array
    {
        return array_map(function ($row) {
            return $row['event'];
        }, $this->response);
    }
}