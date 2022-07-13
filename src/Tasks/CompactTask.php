<?php
declare(strict_types=1);

namespace Level23\Druid\Tasks;

use Level23\Druid\Context\TaskContext;
use Level23\Druid\TuningConfig\TuningConfig;
use Level23\Druid\Interval\IntervalInterface;
use Level23\Druid\TuningConfig\TuningConfigInterface;

class CompactTask implements TaskInterface
{
    protected string $dataSource;

    protected IntervalInterface $interval;

    protected ?string $segmentGranularity;

    protected ?TuningConfigInterface $tuningConfig;

    protected ?TaskContext $context;

    protected ?int $targetCompactionSizeBytes;

    protected ?string $taskId;

    /**
     * CompactTask constructor.
     *
     * This compaction task reads all segments of the interval 2017-01-01/2018-01-01 and results in new segments. Since
     * both segmentGranularity and keepSegmentGranularity are null, the original segment granularity will be remained
     * and not changed after compaction. To control the number of result segments per time chunk, you can set
     * maxRowsPerSegment or numShards. Please note that you can run multiple compactionTasks at the same time. For
     * example, you can run 12 compactionTasks per month instead of running a single task for the entire year.
     *
     * A compaction task internally generates an index task spec for performing compaction work with some fixed
     * parameters. For example, its firehose is always the ingestSegmentSpec, and dimensionsSpec and metricsSpec
     * include all dimensions and metrics of the input segments by default.
     *
     * Compaction tasks will exit with a failure status code, without doing anything, if the interval you specify has
     * no data segments loaded in it (or if the interval you specify is empty).
     *
     * @param string                                        $dataSource
     * @param \Level23\Druid\Interval\IntervalInterface     $interval
     * @param string|null                                   $segmentGranularity
     * @param \Level23\Druid\TuningConfig\TuningConfig|null $tuningConfig
     * @param \Level23\Druid\Context\TaskContext|null       $context
     * @param int|null                                      $targetCompactionSizeBytes
     * @param string|null                                   $taskId
     */
    public function __construct(
        string $dataSource,
        IntervalInterface $interval,
        ?string $segmentGranularity = null,
        ?TuningConfig $tuningConfig = null,
        ?TaskContext $context = null,
        ?int $targetCompactionSizeBytes = null,
        ?string $taskId = null
    ) {
        $this->dataSource                = $dataSource;
        $this->interval                  = $interval;
        $this->segmentGranularity        = $segmentGranularity;
        $this->tuningConfig              = $tuningConfig;
        $this->context                   = $context;
        $this->targetCompactionSizeBytes = $targetCompactionSizeBytes;
        $this->taskId                    = $taskId;
    }

    /**
     * Return the task in a format so that we can send it to druid.
     *
     * @return array<string,int|null|string|array<string,string|int|bool|array<string,string|int>>>
     */
    public function toArray(): array
    {
        $result = [
            'type'       => 'compact',
            'dataSource' => $this->dataSource,
            'ioConfig'   => [
                'type'      => 'compact',
                'inputSpec' => [
                    'type'     => 'interval',
                    'interval' => $this->interval->getInterval(),
                ],
            ],
        ];

        if ($this->targetCompactionSizeBytes !== null && $this->targetCompactionSizeBytes > 0) {
            $result['targetCompactionSizeBytes'] = $this->targetCompactionSizeBytes;
        }

        if ($this->taskId) {
            $result['id'] = $this->taskId;
        }

        if ($this->segmentGranularity) {
            $result['segmentGranularity'] = $this->segmentGranularity;
        }

        if ($this->tuningConfig instanceof TuningConfig) {
            $this->tuningConfig->setType('index');
            $result['tuningConfig'] = $this->tuningConfig->toArray();
        }

        $context = $this->context ? $this->context->toArray() : [];
        if (count($context) > 0) {
            $result['context'] = $context;
        }

        return $result;
    }
}