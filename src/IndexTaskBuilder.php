<?php
declare(strict_types=1);

namespace Level23\Druid;

use Closure;
use InvalidArgumentException;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Concerns\HasAggregations;
use Level23\Druid\Concerns\HasInterval;
use Level23\Druid\Concerns\HasIntervalValidation;
use Level23\Druid\Concerns\HasQueryGranularity;
use Level23\Druid\Concerns\HasSegmentGranularity;
use Level23\Druid\Concerns\HasTuningConfig;
use Level23\Druid\Context\TaskContext;
use Level23\Druid\Firehoses\IngestSegmentFirehose;
use Level23\Druid\Granularities\ArbitraryGranularity;
use Level23\Druid\Granularities\UniformGranularity;
use Level23\Druid\Tasks\IndexTask;
use Level23\Druid\Tasks\TaskInterface;
use Level23\Druid\Transforms\TransformSpec;
use Level23\Druid\Types\DataType;

class IndexTaskBuilder
{
    use HasSegmentGranularity, HasQueryGranularity, HasInterval, HasIntervalValidation, HasTuningConfig, HasAggregations;

    /**
     * @var array
     */
    protected $dimensions = [];

    /**
     * @var bool
     */
    protected $append = false;

    /**
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
     * IndexTaskBuilder constructor.
     *
     * @param \Level23\Druid\DruidClient $client
     * @param string                     $dataSource
     * @param string|null                $firehoseType
     */
    public function __construct(DruidClient $client, string $dataSource, string $firehoseType = null)
    {
        $this->client       = $client;
        $this->dataSource   = $dataSource;
        $this->firehoseType = $firehoseType;
    }

    /**
     * Add a dimension.
     *
     * @param string                               $name
     * @param string|\Level23\Druid\Types\DataType $type
     *
     * @return $this
     */
    public function dimension(string $name, $type = 'string')
    {
        if (is_string($type)) {
            $type = strtolower($type);
            if (!DataType::isValid($type)) {
                throw new InvalidArgumentException(
                    'The given output type is invalid: ' . $type . '. ' .
                    'Allowed are: ' . implode(',', DataType::values())
                );
            }
        }

        $this->dimensions[] = ['name' => $name, 'type' => $type];

        return $this;
    }

    /**
     * Enable append mode. When this is set, we will add the data retrieved from the firehose to the segments, instead
     * of overwriting the data in the segments.
     *
     * @return $this
     */
    public function append()
    {
        $this->append = true;

        return $this;
    }

    /**
     * Execute the index task. We will return the task identifier.
     *
     * @param \Level23\Druid\Context\TaskContext|array $context
     *
     * @return string
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function execute($context = [])
    {
        $task = $this->buildTask($context);

        return $this->client->executeTask($task);
    }

    /**
     * Execute the index task. We will return the task identifier.
     *
     * @param \Level23\Druid\Context\TaskContext|array $context
     *
     * @return string
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function toJson($context = []): string
    {
        $task = $this->buildTask($context);

        $json = \GuzzleHttp\json_encode($task->toArray(), JSON_PRETTY_PRINT);

        return $json;
    }

    /**
     * Return the task as array
     *
     * @param \Level23\Druid\Context\TaskContext|array $context
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function toArray($context = []): array
    {
        $task = $this->buildTask($context);

        return $task->toArray();
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

        $firehose = null;
        switch ($this->firehoseType) {
            case IngestSegmentFirehose::class:

                // First, validate the given from and to. Make sure that these
                // match the beginning and end of an interval.
                $this->validateInterval($this->dataSource, $this->interval);

                $firehose = new IngestSegmentFirehose($this->dataSource, $this->interval);
                break;
        }

        if ($firehose === null) {
            throw new InvalidArgumentException('No firehose known. Currently we only support re-indexing.');
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
    public function transform(Closure $transformBuilder)
    {
        $builder = new TransformBuilder();
        call_user_func($transformBuilder, $builder);
        $builder->getFilter();

        if (!$builder->getTransforms()) {
            return $this;
        }

        $this->transformSpec = new TransformSpec($builder->getTransforms(), $builder->getFilter());

        return $this;
    }

    /**
     * Enable rollup mode
     *
     * @return $this
     */
    public function rollup()
    {
        $this->rollup = true;

        return $this;
    }

    /**
     * Specify that we want to use a UniformGranularity
     *
     * @return $this
     */
    public function uniformGranularity()
    {
        $this->granularityType = UniformGranularity::class;

        return $this;
    }

    /**
     * Specify that we want to use a ArbitraryGranularity
     *
     * @return $this
     */
    public function arbitraryGranularity()
    {
        $this->granularityType = ArbitraryGranularity::class;

        return $this;
    }
}
