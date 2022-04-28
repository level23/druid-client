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
     * @return array<string,int>
     */
    public function pagingIdentifier(): array
    {
        /** @var array<string,array<string,int>> $row */
        $row = $this->getResultRow();

        return $row['pagingIdentifiers'] ?? [];
    }

    /**
     * Return the data in a "normalized" way, so we can easily iterate over it
     *
     * @return array<mixed>
     */
    public function data(): array
    {
        /** @var array<string,array<string,array<mixed>>> $resultRow */
        $resultRow = $this->getResultRow();

        return array_map(function ($row) {
            /** @var array<string, array<mixed>> $row */
            return $row['event'];
        }, $resultRow['events'] ?? []);
    }

    /**
     * @return array<string,array<mixed>>
     */
    protected function getResultRow(): array
    {
        /** @var null|array<string,array<string,array<mixed>>> $row */
        $row = $this->response[0] ?? null;
        if ($row === null) {
            return [];
        }

        return $row['result'] ?? [];
    }
}