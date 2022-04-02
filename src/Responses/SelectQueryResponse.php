<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class SelectQueryResponse extends QueryResponse
{
    /**
     * Return the last known paging identifier known by a select query. (If any is executed).
     * If no paging identifier is known, an empty array is returned.
     *
     * The paging identifier will be something like this:
     * ```
     * Array(
     *   'wikipedia_2015-09-12T00:00:00.000Z_2015-09-13T00:00:00.000Z_2019-09-12T14:15:44.694Z' => 19,
     * )
     * ```
     *
     * @return array
     */
    public function pagingIdentifier(): array
    {
        if (isset($this->response[0]['result']['pagingIdentifiers'])) {
            return $this->response[0]['result']['pagingIdentifiers'];
        }

        return [];
    }

    /**
     * Return the paging identifier.
     *
     * @return array
     * @deprecated Use pagingIdentifier() instead.
     */
    public function getPagingIdentifier(): array
    {
        return $this->pagingIdentifier();
    }

    /**
     * Return the data in a "normalized" way, so we can easily iterate over it
     *
     * @return array
     */
    public function data(): array
    {
        if (!isset($this->response[0]['result']['events'])) {
            return [];
        }

        return array_map(fn($row) => $row['event'], $this->response[0]['result']['events']);
    }
}