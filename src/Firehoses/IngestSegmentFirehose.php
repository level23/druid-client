<?php
declare(strict_types=1);

namespace Level23\Druid\Firehoses;

use Level23\Druid\Interval\Interval;

class IngestSegmentFirehose implements FirehoseInterface
{
    /**
     * @var string
     */
    protected $dataSource;

    /**
     * @var \Level23\Druid\Interval\Interval
     */
    protected $interval;

    /**
     * IngestSegmentFirehose constructor.
     *
     * @param string                           $dataSource
     * @param \Level23\Druid\Interval\Interval $interval
     */
    public function __construct(string $dataSource, Interval $interval)
    {
        $this->dataSource = $dataSource;
        $this->interval   = $interval;
    }

    /**
     * Return the firehose in a format so that we can send it to druid.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'       => 'ingestSegment',
            'dataSource' => $this->dataSource,
            'interval'   => $this->interval->getInterval(),
        ];
    }
}