<?php
declare(strict_types=1);

namespace Level23\Druid\Tasks;

use Closure;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Types\DataType;
use Level23\Druid\Context\TaskContext;
use Level23\Druid\Concerns\HasInterval;
use Level23\Druid\Concerns\HasInputFormat;
use Level23\Druid\Concerns\HasAggregations;
use Level23\Druid\Concerns\HasTuningConfig;
use Level23\Druid\Transforms\TransformSpec;
use Level23\Druid\Dimensions\TimestampSpec;
use Level23\Druid\Types\MultiValueHandling;
use Level23\Druid\Transforms\TransformBuilder;
use Level23\Druid\Dimensions\SpatialDimension;
use Level23\Druid\Concerns\HasQueryGranularity;
use Level23\Druid\InputSources\DruidInputSource;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Concerns\HasSegmentGranularity;
use Level23\Druid\Collections\TransformCollection;
use Level23\Druid\Granularities\UniformGranularity;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\InputSources\InputSourceInterface;
use Level23\Druid\InputFormats\InputFormatInterface;
use Level23\Druid\Granularities\ArbitraryGranularity;
use Level23\Druid\Collections\SpatialDimensionCollection;

class IndexTaskBuilder extends TaskBuilder
{
    use HasSegmentGranularity, HasQueryGranularity, HasInterval, HasTuningConfig, HasAggregations, HasInputFormat;

    /**
     * @var array<array<string,string|bool>>
     */
    protected array $dimensions = [];

    protected SpatialDimensionCollection $spatialDimensions;

    /**
     * The data source where we will write to.
     *
     * @var string
     */
    protected string $dataSource;

    protected ?InputSourceInterface $inputSource;

    protected bool $rollup = false;

    /**
     * Whether this task should be executed parallel.
     *
     * @var bool
     */
    protected bool $parallel = false;

    protected ?TransformSpec $transformSpec = null;

    protected ?TimestampSpec $timestampSpec = null;

    /**
     * Here we remember which type of granularity we want.
     * By default, this is UniformGranularity.
     *
     * @var string
     */
    protected string $granularityType = UniformGranularity::class;

    protected ?InputFormatInterface $inputFormat = null;

    protected bool $appendToExisting = false;

    /**
     * IndexTaskBuilder constructor.
     *
     * @param DruidClient               $druidClient
     * @param string                    $toDataSource Data source where the data will be imported in.
     * @param InputSourceInterface|null $inputSource
     */
    public function __construct(
        DruidClient $druidClient,
        string $toDataSource,
        ?InputSourceInterface $inputSource = null
    ) {
        $this->client            = $druidClient;
        $this->dataSource        = $toDataSource;
        $this->inputSource       = $inputSource;
        $this->spatialDimensions = new SpatialDimensionCollection();
    }

    /**
     * Add a dimension.
     *
     * @param string $name
     * @param string $type
     *
     * @return $this
     */
    public function dimension(string $name, string $type = DataType::STRING): IndexTaskBuilder
    {
        $this->dimensions[] = ['name' => $name, 'type' => DataType::validate($type)];

        return $this;
    }

    /**
     * Add a multi-value dimension.
     *
     * @param string $name
     * @param string $type
     * @param string $multiValueHandling $type
     * @param bool   $createBitmapIndex
     *
     * @return $this
     */
    public function multiValueDimension(
        string $name,
        string $type = DataType::STRING,
        string $multiValueHandling = MultiValueHandling::SORTED_ARRAY,
        bool $createBitmapIndex = true
    ): IndexTaskBuilder {
        $this->dimensions[] = [
            'name'               => $name,
            'type'               => DataType::validate($type),
            'multiValueHandling' => MultiValueHandling::validate($multiValueHandling),
            'createBitmapIndex'  => $createBitmapIndex,
        ];

        return $this;
    }

    /**
     * Add a spatial dimension.
     *
     * @param string   $name Name of the dimension.
     * @param string[] $dims Field names where latitude,longitude data are read from.
     *
     * @return $this
     */
    public function spatialDimension(string $name, array $dims): IndexTaskBuilder
    {
        $this->spatialDimensions->add(new SpatialDimension($name, $dims));

        return $this;
    }

    /**
     * Enable append mode. When this is set, we will add the data retrieved from the firehose to the segments, instead
     * of overwriting the data in the segments.
     *
     * @return $this
     * @deprecated Use appendToExisting() instead.
     */
    public function append(): IndexTaskBuilder
    {
        $this->appendToExisting();

        return $this;
    }

    /**
     * @param string      $column       Input row field to read the primary timestamp from. Regardless of the name of
     *                                  this input field, the primary timestamp will always be stored as a column named
     *                                  __time in your Druid datasource.
     * @param string      $format       Timestamp format. Options are:
     *                                  - iso: ISO8601 with 'T' separator, like "2000-01-01T01:02:03.456"
     *                                  - posix: seconds since epoch
     *                                  - millis: milliseconds since epoch
     *                                  - micro: microseconds since epoch
     *                                  - nano: nanoseconds since epoch
     *                                  - auto: automatically detects ISO (either 'T' or space separator) or millis
     *                                  format
     *                                  - any Joda DateTimeFormat string
     * @param null|string $missingValue Timestamp to use for input records that have a null or missing timestamp
     *                                  column. Should be in ISO8601 format, like "2000-01-01T01:02:03.456", even if
     *                                  you have specified something else for format. Since Druid requires a primary
     *                                  timestamp, this setting can be useful for ingesting datasets that do not have
     *                                  any per-record timestamps at all.
     *
     * @return $this
     */
    public function timestamp(string $column, string $format, ?string $missingValue = null): IndexTaskBuilder
    {
        $this->timestampSpec = new TimestampSpec($column, $format, $missingValue);

        return $this;
    }

    /**
     * @param \Level23\Druid\Context\TaskContext|array<string,string|int|bool> $context
     *
     * @return \Level23\Druid\Tasks\TaskInterface
     */
    protected function buildTask($context): TaskInterface
    {
        if (is_array($context)) {
            $context = new TaskContext($context);
        }

        if ($this->queryGranularity === null) {
            throw new InvalidArgumentException('You have to specify a queryGranularity value!');
        }

        if ($this->interval === null) {
            throw new InvalidArgumentException('You have to specify an interval!');
        }

        if ($this->timestampSpec === null) {
            throw new InvalidArgumentException('You have to specify an timestamp column!');
        }

        if ($this->granularityType == ArbitraryGranularity::class) {
            $granularity = new ArbitraryGranularity(
                $this->queryGranularity,
                $this->rollup,
                new IntervalCollection($this->interval)
            );
        } else {
            if ($this->segmentGranularity === null) {
                throw new InvalidArgumentException('You have to specify a segmentGranularity value!');
            }

            $granularity = new UniformGranularity(
                $this->segmentGranularity,
                $this->queryGranularity,
                $this->rollup,
                new IntervalCollection($this->interval)
            );
        }

        // No input source known? Then use our deprecated "string" approach.
        if (!isset($this->inputSource)) {
            throw new InvalidArgumentException(
                'No InputSource known. You have to supply an input source!.'
            );
        }

        // Do we want to read data from duid? And no interval set yet? Then fill it. We assume this is a reindex task.
        if ($this->inputSource instanceof DruidInputSource && $this->inputSource->getInterval() === null) {
            $this->inputSource->setInterval($this->interval);
        }

        $task = new IndexTask(
            $this->dataSource,
            $this->inputSource,
            $granularity,
            $this->timestampSpec,
            $this->transformSpec,
            $this->tuningConfig,
            $context,
            new AggregationCollection(... $this->aggregations),
            $this->dimensions,
            $this->taskId,
            $this->inputFormat,
            $this->spatialDimensions
        );

        if ($this->parallel) {
            $task->setParallel($this->parallel);
        }

        if ($this->appendToExisting) {
            $task->setAppendToExisting($this->appendToExisting);
        }

        return $task;
    }

    /**
     * Call this with a closure. Your closure will receive a TransformBuilder, which allows you to
     * specify a transform which needs to be applied when this indexing job is executed. Optionally you can
     * also specify a filter on which records this transform needs to be applied.
     *
     * Note: calling this method more than once will overwrite the previous data!
     *
     * @param \Closure $transformBuilder
     *
     * @return $this
     */
    public function transform(Closure $transformBuilder): IndexTaskBuilder
    {
        $builder = new TransformBuilder();
        call_user_func($transformBuilder, $builder);

        if (!$builder->getTransforms()) {
            return $this;
        }

        $this->transformSpec = new TransformSpec(
            new TransformCollection(...$builder->getTransforms()),
            $builder->getFilter()
        );

        return $this;
    }

    /**
     * Enable rollup mode
     *
     * @return $this
     */
    public function rollup(): IndexTaskBuilder
    {
        $this->rollup = true;

        return $this;
    }

    /**
     * @param \Level23\Druid\InputSources\InputSourceInterface $inputSource
     *
     * @return \Level23\Druid\Tasks\IndexTaskBuilder
     */
    public function inputSource(InputSourceInterface $inputSource): IndexTaskBuilder
    {
        $this->inputSource = $inputSource;

        return $this;
    }

    /**
     * Execute this index task as parallel batch.
     *
     * @return \Level23\Druid\Tasks\IndexTaskBuilder
     */
    public function parallel(): IndexTaskBuilder
    {
        $this->parallel = true;

        return $this;
    }

    /**
     * Specify that we want to use a UniformGranularity
     *
     * @return $this
     */
    public function uniformGranularity(): IndexTaskBuilder
    {
        $this->granularityType = UniformGranularity::class;

        return $this;
    }

    /**
     * Specify that we want to use a ArbitraryGranularity
     *
     * @return $this
     */
    public function arbitraryGranularity(): IndexTaskBuilder
    {
        $this->granularityType = ArbitraryGranularity::class;

        return $this;
    }

    /**
     * Creates segments as additional shards of the latest version, effectively appending to the segment set instead of
     * replacing it. This means that you can append new segments to any datasource regardless of its original
     * partitioning scheme. You must use the dynamic partitioning type for the appended segments. If you specify a
     * different partitioning type, the task fails with an error.
     *
     * @param bool $appendToExisting
     *
     * @return \Level23\Druid\Tasks\IndexTaskBuilder
     */
    public function appendToExisting(bool $appendToExisting = true): IndexTaskBuilder
    {
        $this->appendToExisting = $appendToExisting;

        return $this;
    }
}
