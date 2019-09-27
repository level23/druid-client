<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class SelectQueryResponse extends QueryResponse
{
    /**
     * Return the last known paging identifier known by a select query. (If any is executed).
     * If no paging identifier is known, an empty array is returned.
     *
     * @return array
     */
    public function getPagingIdentifier(): array
    {
        if (isset($this->response[0]['result']['pagingIdentifiers'])) {
            return $this->response[0]['result']['pagingIdentifiers'];
        }

        return [];
    }

    /**
     * Return the data in a "normalized" way so we can easily iterate over it
     *
     * @return array
     */
    public function data(): array
    {
        return array_map(function ($row) {
            return $row['event'];
        }, $this->response[0]['result']['events']);
    }
}