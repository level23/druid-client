<?php
declare(strict_types=1);

namespace Level23\Druid;

use InvalidArgumentException;
use Level23\Druid\Concerns\HasInterval;
use Level23\Druid\Concerns\HasIntervalValidation;
use Level23\Druid\Concerns\HasSegmentGranularity;
use Level23\Druid\Concerns\HasTuningConfig;
use Level23\Druid\Context\TaskContext;
use Level23\Druid\Interval\IntervalInterface;
use Level23\Druid\Tasks\CompactTask;

class CompactTaskBuilder
{
    use HasInterval, HasIntervalValidation, HasSegmentGranularity, HasTuningConfig;

    /**
     * @var string
     */
    protected $dataSource;

    /**
     * @var \Level23\Druid\DruidClient
     */
    protected $client;

    /**
     * @var int
     */
    protected $targetCompactionSizeBytes;

    /**
     * CompactTaskBuilder constructor.
     *
     * A compaction task internally generates an index task spec for performing compaction work with some fixed
     * parameters. For example, its firehose is always the ingestSegmentSpec, and dimensionsSpec and metricsSpec
     * include all dimensions and metrics of the input segments by default.
     *
     * Compaction tasks will exit with a failure status code, without doing anything, if the interval you specify has
     * no data segments loaded in it (or if the interval you specify is empty).
     *
     * @param \Level23\Druid\DruidClient $client
     * @param string                     $dataSource
     */
    public function __construct(DruidClient $client, string $dataSource)
    {
        $this->dataSource = $dataSource;
        $this->client     = $client;
    }

    /**
     * @param \Level23\Druid\Context\TaskContext|array $taskContext
     *
     * @return string
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function execute($taskContext = null)
    {
        if (is_array($taskContext)) {
            $taskContext = new TaskContext($taskContext);
        }

        if (!$this->interval instanceof IntervalInterface) {
            throw new InvalidArgumentException('You have to specify an interval!');
        }

        // First, validate the given from and to. Make sure that these
        // match the beginning and end of an interval.
        $this->validateInterval($this->dataSource, $this->interval);

        $task = new CompactTask(
            $this->dataSource,
            $this->interval,
            $this->segmentGranularity,
            $this->tuningConfig,
            $taskContext
        );

        // @todo: add support for building metricSpec and DimensionSpec.

        return $this->client->executeTask($task);
    }

    /**
     * @param int $bytes
     *
     * @return $this
     */
    public function targetCompactionSize(int $bytes)
    {
        $this->targetCompactionSizeBytes = $bytes;

        return $this;
    }
}