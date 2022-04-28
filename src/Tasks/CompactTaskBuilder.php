<?php
declare(strict_types=1);

namespace Level23\Druid\Tasks;

use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Context\TaskContext;
use Level23\Druid\Concerns\HasInterval;
use Level23\Druid\Concerns\HasTuningConfig;
use Level23\Druid\Interval\IntervalInterface;
use Level23\Druid\Concerns\HasSegmentGranularity;

class CompactTaskBuilder extends TaskBuilder
{
    use HasInterval, HasSegmentGranularity, HasTuningConfig;

    protected string $dataSource;

    protected ?int $targetCompactionSizeBytes = null;

    /**
     * CompactTaskBuilder constructor.
     *
     * A compaction task internally generates an index task spec for performing compaction work with some fixed
     * parameters. For example, its input source is always DruidInputSource, and dimensionsSpec and metricsSpec
     * include all dimensions and metrics of the input segments by default.
     *
     * Compaction tasks will exit with a failure status code, without doing anything, if the interval you specify has
     * no data segments loaded in it (or if the interval you specify is empty).
     *
     * @param string $dataSource
     */
    public function __construct(DruidClient $druidClient, string $dataSource)
    {
        $this->client     = $druidClient;
        $this->dataSource = $dataSource;
    }

    /**
     * @param \Level23\Druid\Context\TaskContext|array<string,string|int|bool> $context
     *
     * @return \Level23\Druid\Tasks\CompactTask
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function buildTask($context): TaskInterface
    {
        if (is_array($context)) {
            $context = new TaskContext($context);
        }

        $interval = $this->interval;
        if (!$interval instanceof IntervalInterface) {
            throw new InvalidArgumentException('You have to specify an interval!');
        }

        // First, validate the given from and to. Make sure that these
        // match the beginning and end of an interval.
        $properties = $context->toArray();
        if (empty($properties['skipIntervalValidation'])) {
            $this->validateInterval($this->dataSource, $interval);
        }

        // @todo: add support for building metricSpec and DimensionSpec.

        return new CompactTask(
            $this->dataSource,
            $interval,
            $this->segmentGranularity,
            $this->tuningConfig,
            $context,
            $this->targetCompactionSizeBytes,
            $this->taskId
        );
    }

    /**
     * @param int $bytes
     *
     * @return $this
     */
    public function targetCompactionSize(int $bytes): CompactTaskBuilder
    {
        $this->targetCompactionSizeBytes = $bytes;

        return $this;
    }
}