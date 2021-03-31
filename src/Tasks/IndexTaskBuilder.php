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
use Level23\Druid\Transforms\TransformBuilder;
use Level23\Druid\Concerns\HasQueryGranularity;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Concerns\HasSegmentGranularity;
use Level23\Druid\Firehoses\IngestSegmentFirehose;
use Level23\Druid\Collections\TransformCollection;
use Level23\Druid\Granularities\UniformGranularity;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Granularities\ArbitraryGranularity;

class IndexTaskBuilder extends TaskBuilder
{
    use HasSegmentGranularity, HasQueryGranularity, HasInterval, HasTuningConfig, HasAggregations;

    /**
     * @var array
     */
    protected $dimensions = [];

    /**
     * @var bool
     */
    protected $append = false;

    /**
     * The data source where we will write to.
     *
     * @var string
     */
    protected $dataSource;

    /**
     * @var string|null
     */
    protected $firehoseType;

    /**
     * @var bool
     */
    protected $rollup = false;

    /**
     * Whether or not this task should be executed parallel.
     *
     * @var bool
     */
    protected $parallel = false;

    /**
     * @var TransformSpec|null
     */
    protected $transformSpec;

    /**
     * Here we remember which type of granularity we want.
     * By default this is UniformGranularity.
     *
     * @var string
     */
    protected $granularityType = UniformGranularity::class;

    /**
     * The data source where the data will be read from.
     * This will only be used in case of IngestSegmentFirehose.
     *
     * @var string
     */
    protected $fromDataSource;

    /**
     * IndexTaskBuilder constructor.
     *
     * @param \Level23\Druid\DruidClient $client
     * @param string                     $toDataSource Data source where the data will be imported in.
     * @param string|null                $firehoseType The type of FireHose to use (where to retrieve the data from).
     */
    public function __construct(DruidClient $client, string $toDataSource, string $firehoseType = null)
    {
        $this->client       = $client;
        $this->dataSource   = $toDataSource;
        $this->firehoseType = $firehoseType;
    }

    /**
     * Add a dimension.
     *
     * @param string $name
     * @param string $type
     *
     * @return $this
     */
    public function dimension(string $name, $type = DataType::STRING): IndexTaskBuilder
    {
        $this->dimensions[] = ['name' => $name, 'type' => DataType::validate($type)];

        return $this;
    }

    /**
     * Enable append mode. When this is set, we will add the data retrieved from the firehose to the segments, instead
     * of overwriting the data in the segments.
     *
     * @return $this
     */
    public function append(): IndexTaskBuilder
    {
        $this->append = true;

        return $this;
    }

    /**
     * The data source where the data will be read from. This will only be used in case of IngestSegmentFirehose.
     *
     * @param string $fromDataSource
     *
     * @return $this
     */
    public function fromDataSource(string $fromDataSource)
    {
        $this->fromDataSource = $fromDataSource;

        return $this;
    }

    /**
     * The data source where the data will be read from. This will only be used in case of IngestSegmentFirehose.
     *
     * @param string $fromDataSource
     *
     * @return $this
     * @deprecated Use fromDataSource() instead
     *
     */
    public function setFromDataSource(string $fromDataSource)
    {
        return $this->fromDataSource($fromDataSource);
    }

    /**
     * @param \Level23\Druid\Context\TaskContext|array $context
     *
     * @return \Level23\Druid\Tasks\TaskInterface
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    protected function buildTask($context): TaskInterface
    {
        if ($this->queryGranularity === null) {
            throw new InvalidArgumentException('You have to specify a queryGranularity value!');
        }

        if ($this->interval === null) {
            throw new InvalidArgumentException('You have to specify an interval!');
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

        switch ($this->firehoseType) {
            case IngestSegmentFirehose::class:
                $fromDataSource = $this->fromDataSource ?? $this->dataSource;

                // First, validate the given from and to. Make sure that these
                // match the beginning and end of an interval.
                $this->validateInterval($fromDataSource, $this->interval);

                $firehose = new IngestSegmentFirehose($fromDataSource, $this->interval);
                break;

            default:
                throw new InvalidArgumentException(
                    'No firehose known. Currently we only support re-indexing (IngestSegmentFirehose).'
                );
        }

        if (!$context instanceof TaskContext) {
            $context = new TaskContext($context);
        }

        $task = new IndexTask(
            $this->dataSource,
            $firehose,
            $granularity,
            $this->transformSpec,
            $this->tuningConfig,
            $context,
            new AggregationCollection(... $this->aggregations),
            $this->dimensions
        );

        if ($this->parallel) {
            $task->setParallel($this->parallel);
        }

        return $task;
    }

    /**
     * Call this with a closure. Your closure will receive a TransformBuilder, which allows you to
     * specify a transform which needs to be applied when this indexing job is executed. Optionally you can
     * also specify a filter on which records this transform needs to be applied.
     *
     * Note: calling this method more then once will overwrite the previous data!
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
     * Execute this index task as parallel batch.
     */
    public function parallel()
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
}
