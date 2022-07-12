<?php
declare(strict_types=1);

namespace Level23\Druid\Tasks;

use InvalidArgumentException;
use Level23\Druid\Context\TaskContext;
use Level23\Druid\Transforms\TransformSpec;
use Level23\Druid\Dimensions\TimestampSpec;
use Level23\Druid\TuningConfig\TuningConfig;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\InputSources\InputSourceInterface;
use Level23\Druid\InputFormats\InputFormatInterface;
use Level23\Druid\Granularities\GranularityInterface;
use Level23\Druid\Collections\SpatialDimensionCollection;

class IndexTask implements TaskInterface
{
    protected ?TuningConfig $tuningConfig;

    protected ?TaskContext $context;

    protected bool $appendToExisting = false;

    protected InputSourceInterface $inputSource;

    protected string $dateSource;

    protected GranularityInterface $granularity;

    protected ?TransformSpec $transformSpec;

    protected ?AggregationCollection $aggregations;

    /**
     * @var array<array<string,string|bool>>
     */
    protected array $dimensions = [];

    /**
     * Whether this task should be executed parallel.
     *
     * @var bool
     */
    protected bool $parallel = false;

    protected ?string $taskId;

    protected ?InputFormatInterface $inputFormat;

    protected ?TimestampSpec $timestampSpec;

    protected ?SpatialDimensionCollection $spatialDimensions = null;

    /**
     * IndexTask constructor.
     *
     * @param string                                                     $dateSource
     * @param \Level23\Druid\InputSources\InputSourceInterface           $inputSource
     * @param \Level23\Druid\Granularities\GranularityInterface          $granularity
     * @param \Level23\Druid\Transforms\TransformSpec|null               $transformSpec
     * @param \Level23\Druid\TuningConfig\TuningConfig|null              $tuningConfig
     * @param \Level23\Druid\Context\TaskContext|null                    $context
     * @param \Level23\Druid\Collections\AggregationCollection|null      $aggregations
     * @param array<array<string,string|bool>>                           $dimensions
     * @param string|null                                                $taskId
     * @param \Level23\Druid\InputFormats\InputFormatInterface|null      $inputFormat
     * @param \Level23\Druid\Dimensions\TimestampSpec|null               $timestampSpec
     * @param \Level23\Druid\Collections\SpatialDimensionCollection|null $spatialDimensions
     */
    public function __construct(
        string $dateSource,
        InputSourceInterface $inputSource,
        GranularityInterface $granularity,
        TransformSpec $transformSpec = null,
        TuningConfig $tuningConfig = null,
        TaskContext $context = null,
        AggregationCollection $aggregations = null,
        array $dimensions = [],
        string $taskId = null,
        InputFormatInterface $inputFormat = null,
        TimestampSpec $timestampSpec = null,
        SpatialDimensionCollection $spatialDimensions = null
    ) {
        $this->tuningConfig      = $tuningConfig;
        $this->context           = $context;
        $this->inputSource       = $inputSource;
        $this->dateSource        = $dateSource;
        $this->granularity       = $granularity;
        $this->transformSpec     = $transformSpec;
        $this->aggregations      = $aggregations;
        $this->dimensions        = $dimensions;
        $this->taskId            = $taskId;
        $this->inputFormat       = $inputFormat;
        $this->timestampSpec     = $timestampSpec;
        $this->spatialDimensions = $spatialDimensions;
    }

    /**
     * Return the task in a format so that we can send it to druid.
     *
     * @return array<string,array<string,array<string,array<int|string,array<mixed>|bool|int|string>|bool|int|string|null>|bool|int|string>|string>
     */
    public function toArray(): array
    {
        if (empty($this->timestampSpec)) {
            throw new InvalidArgumentException('You have to specify your timestamp column!');
        }

        $result = [
            'type' => $this->parallel ? 'index_parallel' : 'index',
            'spec' => [
                'dataSchema' => [
                    'dataSource'      => $this->dateSource,
                    'timestampSpec'   => $this->timestampSpec->toArray(),
                    'dimensionsSpec'  => [
                        'dimensions' => $this->dimensions,
                    ],
                    'metricsSpec'     => ($this->aggregations ? $this->aggregations->toArray() : null),
                    'granularitySpec' => $this->granularity->toArray(),
                    'transformSpec'   => ($this->transformSpec ? $this->transformSpec->toArray() : null),
                ],
                'ioConfig'   => [
                    'type'             => $this->parallel ? 'index_parallel' : 'index',
                    'inputSource'      => $this->inputSource->toArray(),
                    'inputFormat'      => $this->inputFormat ? $this->inputFormat->toArray() : null,
                    'appendToExisting' => $this->appendToExisting,
                ],
            ],
        ];

        // Add our spatial dimensions if supplied.
        if (!empty($this->spatialDimensions)) {
            $result['spec']['dataSchema']['dimensionsSpec']['spatialDimensions'] = $this->spatialDimensions->toArray();
        }

        $context = $this->context ? $this->context->toArray() : [];
        if (count($context) > 0) {
            $result['context'] = $context;
        }

        if ($this->tuningConfig) {
            $this->tuningConfig->setType($this->parallel ? 'index_parallel' : 'index');
            $result['spec']['tuningConfig'] = $this->tuningConfig->toArray();
        }

        if ($this->taskId) {
            $result['id'] = $this->taskId;
        }

        return $result;
    }

    /**
     * @param bool $appendToExisting
     */
    public function setAppendToExisting(bool $appendToExisting): void
    {
        $this->appendToExisting = $appendToExisting;
    }

    /**
     * Whether this task should be executed parallel.
     *
     * @param bool $parallel
     */
    public function setParallel(bool $parallel): void
    {
        $this->parallel = $parallel;
    }

    /**
     * @param \Level23\Druid\Dimensions\TimestampSpec $timestampSpec
     */
    public function setTimestampSpec(TimestampSpec $timestampSpec): void
    {
        $this->timestampSpec = $timestampSpec;
    }

    /**
     * @param InputFormatInterface $inputFormat
     *
     * @return IndexTask
     */
    public function setInputFormat(InputFormatInterface $inputFormat): IndexTask
    {
        $this->inputFormat = $inputFormat;

        return $this;
    }
}