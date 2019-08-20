<?php
declare(strict_types=1);

namespace Level23\Druid\Tasks;

use Level23\Druid\Context\TaskContext;
use Level23\Druid\Interval\IntervalInterface;
use Level23\Druid\TuningConfig\TuningConfig;

class CompactTask implements TaskInterface
{
    /**
     * @var string
     */
    protected $dataSource;

    /**
     * @var \Level23\Druid\Interval\IntervalInterface
     */
    protected $interval;

    /**
     * @var string|\Level23\Druid\Types\Granularity|null
     */
    protected $segmentGranularity;

    /**
     * @var \Level23\Druid\TuningConfig\TuningConfigInterface|null
     */
    protected $tuningConfig;

    /**
     * @var \Level23\Druid\Context\TaskContext|null
     */
    protected $context;

    /**
     * @var int|null
     */
    protected $targetCompactionSizeBytes;

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
     * @param null|string|\Level23\Druid\Types\Granularity  $segmentGranularity
     * @param \Level23\Druid\TuningConfig\TuningConfig|null $tuningConfig
     * @param \Level23\Druid\Context\TaskContext|null       $context
     * @param int|null                                      $targetCompactionSizeBytes
     */
    public function __construct(
        string $dataSource,
        IntervalInterface $interval,
        $segmentGranularity = null,
        TuningConfig $tuningConfig = null,
        TaskContext $context = null,
        int $targetCompactionSizeBytes = null
    ) {
        $this->dataSource                = $dataSource;
        $this->interval                  = $interval;
        $this->segmentGranularity        = $segmentGranularity;
        $this->tuningConfig              = $tuningConfig;
        $this->context                   = $context;
        $this->targetCompactionSizeBytes = $targetCompactionSizeBytes;
    }

    /**
     * Return the task in a format so that we can send it to druid.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'type'       => 'compact',
            'dataSource' => $this->dataSource,
            'interval'   => $this->interval->getInterval(),
        ];

        if ($this->segmentGranularity) {
            $result['segmentGranularity'] = $this->segmentGranularity;
        }

        if ($this->targetCompactionSizeBytes) {
            $result['targetCompactionSizeBytes'] = $this->targetCompactionSizeBytes;
        }

        if ($this->tuningConfig instanceof TuningConfig) {
            $this->tuningConfig->type = 'index';
            $result['tuningConfig']   = $this->tuningConfig->toArray();
        }

        if ($this->context) {
            $result['context'] = $this->context->toArray();
        }

        return $result;
    }
}