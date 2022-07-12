<?php
declare(strict_types=1);

namespace Level23\Druid\Tasks;

use Closure;
use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Types\DataType;
use Level23\Druid\Context\TaskContext;
use Level23\Druid\Concerns\HasInterval;
use Level23\Druid\Concerns\HasAggregations;
use Level23\Druid\Concerns\HasTuningConfig;
use Level23\Druid\Transforms\TransformSpec;
use Level23\Druid\Dimensions\TimestampSpec;
use Level23\Druid\InputFormats\FlattenSpec;
use Level23\Druid\Types\MultiValueHandling;
use Level23\Druid\Transforms\TransformBuilder;
use Level23\Druid\Dimensions\SpatialDimension;
use Level23\Druid\InputFormats\CsvInputFormat;
use Level23\Druid\InputFormats\TsvInputFormat;
use Level23\Druid\InputFormats\OrcInputFormat;
use Level23\Druid\Concerns\HasQueryGranularity;
use Level23\Druid\InputFormats\JsonInputFormat;
use Level23\Druid\InputSources\DruidInputSource;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Concerns\HasSegmentGranularity;
use Level23\Druid\Collections\TransformCollection;
use Level23\Druid\InputFormats\ParquetInputFormat;
use Level23\Druid\Granularities\UniformGranularity;
use Level23\Druid\InputFormats\ProtobufInputFormat;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\InputSources\InputSourceInterface;
use Level23\Druid\InputFormats\InputFormatInterface;
use Level23\Druid\Granularities\ArbitraryGranularity;
use Level23\Druid\Collections\SpatialDimensionCollection;

class IndexTaskBuilder extends TaskBuilder
{
    use HasSegmentGranularity, HasQueryGranularity, HasInterval, HasTuningConfig, HasAggregations;

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

    /**
     * @var TransformSpec|null
     */
    protected ?TransformSpec $transformSpec = null;

    /**
     * @var TimestampSpec|null
     */
    protected ?TimestampSpec $timestampSpec = null;

    /**
     * Here we remember which type of granularity we want.
     * By default, this is UniformGranularity.
     *
     * @var string
     */
    protected string $granularityType = UniformGranularity::class;

    /**
     * @var \Level23\Druid\InputFormats\InputFormatInterface|null
     */
    protected ?InputFormatInterface $inputFormat = null;

    /**
     * @var bool
     */
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
     * Specify that we use JSON as input format.
     *
     * @param FlattenSpec|null        $flattenSpec Specifies flattening configuration for nested JSON data. See
     *                                             flattenSpec for more info.
     * @param array<string,bool>|null $features    JSON parser features supported by Jackson library. Those features
     *                                             will be applied when parsing the input JSON data.
     *
     * @see https://github.com/FasterXML/jackson-core/wiki/JsonParser-Features
     */
    public function jsonFormat(FlattenSpec $flattenSpec = null, array $features = null): self
    {
        $this->inputFormat = new JsonInputFormat($flattenSpec, $features);

        return $this;
    }

    /**
     * Specify that we use CSV as input format.
     *
     * @param string[]|null $columns               Specifies the columns of the data. The columns should be in the same
     *                                             order with the columns of your data.
     * @param string|null   $listDelimiter         A custom delimiter for multi-value dimensions.
     * @param bool|null     $findColumnsFromHeader If this is set, the task will find the column names from the header
     *                                             row. Note that skipHeaderRows will be applied before finding column
     *                                             names from the header. For example, if you set skipHeaderRows to 2
     *                                             and findColumnsFromHeader to true, the task will skip the first two
     *                                             lines and then extract column information from the third line.
     *                                             columns will be ignored if this is set to true.
     * @param int           $skipHeaderRows        If this is set, the task will skip the first skipHeaderRows rows.
     */
    public function csvFormat(
        array $columns = null,
        string $listDelimiter = null,
        bool $findColumnsFromHeader = null,
        int $skipHeaderRows = 0
    ): self {
        $this->inputFormat = new CsvInputFormat($columns, $listDelimiter, $findColumnsFromHeader, $skipHeaderRows);

        return $this;
    }

    /**
     * Specify that we use TSV as input format.
     *
     * @param array<string>|null $columns               Specifies the columns of the data. The columns should be in the
     *                                                  same order with the columns of your data.
     * @param string|null        $delimiter             A custom delimiter for data values.
     * @param string|null        $listDelimiter         A custom delimiter for multi-value dimensions.
     * @param bool|null          $findColumnsFromHeader If this is set, the task will find the column names from the
     *                                                  header row. Note that skipHeaderRows will be applied before
     *                                                  finding column names from the header. For example, if you set
     *                                                  skipHeaderRows to 2 and findColumnsFromHeader to true, the task
     *                                                  will skip the first two lines and then extract column
     *                                                  information from the third line. columns will be ignored if
     *                                                  this is set to true.
     * @param int                $skipHeaderRows        If this is set, the task will skip the first skipHeaderRows
     *                                                  rows.
     */
    public function tsvFormat(
        array $columns = null,
        string $delimiter = null,
        string $listDelimiter = null,
        bool $findColumnsFromHeader = null,
        int $skipHeaderRows = 0
    ): self {
        $this->inputFormat = new TsvInputFormat(
            $columns,
            $delimiter,
            $listDelimiter,
            $findColumnsFromHeader,
            $skipHeaderRows
        );

        return $this;
    }

    /**
     * Specify that we use ORC as input format.
     *
     * To use the ORC input format, load the Druid Orc extension ( druid-orc-extensions).
     *
     * @param FlattenSpec|null $flattenSpec    Specifies flattening configuration for nested ORC data. See flattenSpec
     *                                         for more info.
     * @param bool|null        $binaryAsString Specifies if the binary orc column which is not logically marked as a
     *                                         string should be treated as a UTF-8 encoded string. Default is false.
     */
    public function orcFormat(FlattenSpec $flattenSpec = null, bool $binaryAsString = null): self
    {
        $this->inputFormat = new OrcInputFormat($flattenSpec, $binaryAsString);

        return $this;
    }

    /**
     * Specify that we use Parquet as input format.
     *
     * To use the Parquet input format load the Druid Parquet extension (druid-parquet-extensions).
     *
     * @param FlattenSpec|null $flattenSpec    Define a flattenSpec to extract nested values from a Parquet file. Note
     *                                         that only 'path' expression are supported ('jq' is unavailable).
     * @param bool|null        $binaryAsString Specifies if the bytes parquet column which is not logically marked as a
     *                                         string or enum type should be treated as a UTF-8 encoded string.
     */
    public function parquetFormat(FlattenSpec $flattenSpec = null, bool $binaryAsString = null): self
    {
        $this->inputFormat = new ParquetInputFormat($flattenSpec, $binaryAsString);

        return $this;
    }

    /**
     * Specify that we use Protobuf as input format.
     *
     * You need to include the druid-protobuf-extensions as an extension to use the Protobuf input format.
     *
     * @param array<string,string> $protoBytesDecoder Specifies how to decode bytes to Protobuf record. See below for
     *                                                an example.
     * @param FlattenSpec|null     $flattenSpec       Define a flattenSpec to extract nested values from a Parquet
     *                                                file. Note that only 'path' expression are supported ('jq' is
     *                                                unavailable).
     *
     * Example $protoBytesDecoder value:
     * ```
     * [
     *     "type" => "file",
     *     "descriptor" => "file:///tmp/metrics.desc",
     *     "protoMessageType" => "Metrics"
     * ]
     * ```
     *
     * @see https://druid.apache.org/docs/latest/ingestion/data-formats.html#protobuf
     */
    public function protobufFormat(array $protoBytesDecoder, FlattenSpec $flattenSpec = null): self
    {
        $this->inputFormat = new ProtobufInputFormat($protoBytesDecoder, $flattenSpec);

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
            $this->transformSpec,
            $this->tuningConfig,
            $context,
            new AggregationCollection(... $this->aggregations),
            $this->dimensions,
            $this->taskId,
            $this->inputFormat,
            $this->timestampSpec,
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
