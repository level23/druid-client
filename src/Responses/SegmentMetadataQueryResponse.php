<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class SegmentMetadataQueryResponse extends QueryResponse
{
    /**
     * Return the data in a "normalized" way, so we can easily iterate over it
     *
     * @return array<array<string,string>>
     */
    public function data(): array
    {
        return $this->response;
    }
}