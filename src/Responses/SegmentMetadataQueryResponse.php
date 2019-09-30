<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class SegmentMetadataQueryResponse extends QueryResponse
{
    /**
     * Return the data in a "normalized" way so we can easily iterate over it
     *
     * @return array
     */
    public function data(): array
    {
        $columns = [];
        if (isset($this->response[0]['columns'])) {
            array_walk($this->response[0]['columns'], function ($value, $key) use (&$columns) {
                $columns[] = array_merge($value, ['field' => $key]);
            });
        }

        return $columns;
    }
}