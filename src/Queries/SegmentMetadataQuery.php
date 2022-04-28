<?php
declare(strict_types=1);

namespace Level23\Druid\Queries;

use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\DataSources\DataSourceInterface;
use Level23\Druid\Responses\SegmentMetadataQueryResponse;

class SegmentMetadataQuery implements QueryInterface
{
    protected DataSourceInterface $dataSource;

    protected IntervalCollection $intervals;

    public function __construct(DataSourceInterface $dataSource, IntervalCollection $intervals)
    {
        $this->dataSource = $dataSource;
        $this->intervals  = $intervals;
    }

    /**
     * Return the query in array format, so we can fire it to druid.
     *
     * @return array<string,array<int|string,array<string>|string>|string>
     */
    public function toArray(): array
    {
        return [
            'queryType'  => 'segmentMetadata',
            'dataSource' => $this->dataSource->toArray(),
            'intervals'  => $this->intervals->toArray(),
        ];
    }

    /**
     * Parse the response into something we can return to the user.
     *
     * @param array<string|int,array<mixed>|int|string> $response
     *
     * @return SegmentMetadataQueryResponse
     */
    public function parseResponse(array $response): SegmentMetadataQueryResponse
    {
        return new SegmentMetadataQueryResponse($response);
    }
}