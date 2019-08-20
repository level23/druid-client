<?php
declare(strict_types=1);

namespace Level23\Druid\Queries;

use Level23\Druid\Collections\IntervalCollection;

class SegmentMetadataQuery implements QueryInterface
{
    /**
     * @var string
     */
    protected $dataSource;

    /**
     * @var \Level23\Druid\Collections\IntervalCollection
     */
    protected $intervals;

    public function __construct(string $dataSource, IntervalCollection $intervals)
    {
        $this->dataSource = $dataSource;
        $this->intervals  = $intervals;
    }

    /**
     * Return the query in array format so we can fire it to druid.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'queryType'  => 'segmentMetadata',
            'dataSource' => $this->dataSource,
            'intervals'  => $this->intervals->toArray(),
        ];
    }

    /**
     * Parse the response into something we can return to the user.
     *
     * @param array $response
     *
     * @return array
     */
    public function parseResponse(array $response): array
    {
        return $response;
    }
}