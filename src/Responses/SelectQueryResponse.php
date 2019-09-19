<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class SelectQueryResponse extends QueryResponse
{
    /**
     * @var array
     */
    protected $pagingIdentifier = [];

    public function __construct(array $response)
    {
        $this->rawResponse = $response;

        if (isset($response[0])) {
            $this->response = array_map(function ($row) {
                return $row['event'];
            }, $response[0]['result']['events']);

            $this->pagingIdentifier = $response[0]['result']['pagingIdentifiers'];
        } else {
            $this->response         = [];
            $this->pagingIdentifier = [];
        }
    }

    /**
     * Return the last known paging identifier known by a select query. (If any is executed).
     * If no paging identifier is known, an empty array is returned.
     *
     * @return array
     */
    public function getPagingIdentifier(): array
    {
        return $this->pagingIdentifier;
    }
}