<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class TimeSeriesQueryResponse extends QueryResponse
{
    protected string $timeOutputName;

    /**
     * TimeSeriesQueryResponse constructor.
     *
     * @param array<string|int,string|int|array<mixed>> $response $response
     * @param string                                    $timeOutputName
     */
    public function __construct(array $response, string $timeOutputName)
    {
        parent::__construct($response);

        $this->timeOutputName = $timeOutputName;
    }

    /**
     * Return the data in a "normalized" way, so we can easily iterate over it
     *
     * @return array<array<mixed>>
     */
    public function data(): array
    {
        return array_map(function ($row) {
            /** @var array<string,array<array<mixed>>> $row */
            $row['result'][$this->timeOutputName] = $row['timestamp'];

            return $row['result'];
        }, $this->response);
    }
}