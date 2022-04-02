<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class TimeSeriesQueryResponse extends QueryResponse
{
    protected string $timeOutputName;

    /**
     * TimeSeriesQueryResponse constructor.
     *
     * @param array  $response
     * @param string $timeOutputName
     */
    public function __construct(array $response, string $timeOutputName)
    {
        parent::__construct($response);

        $this->timeOutputName = $timeOutputName;
    }

    /**
     * Return the data in a "normalized" way, so we can easily iterate over it
     *
     * @return array
     */
    public function data(): array
    {
        return array_map(function ($row) {
            $row['result'][$this->timeOutputName] = $row['timestamp'];

            return $row['result'];
        }, $this->response);
    }
}