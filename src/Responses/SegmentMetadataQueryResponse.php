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
        $columns = [];
        if (isset($this->response[0])) {

            /** @var array<string,array<string,array<string,string>>> $row */
            $row = $this->response[0];

            if(isset($row['columns'])) {
                array_walk($row['columns'], function ($value, $key) use (&$columns) {
                    $columns[] = array_merge($value, ['field' => $key]);
                });
            }
        }

        return $columns;
    }
}