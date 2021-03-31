<?php
declare(strict_types=1);

namespace Level23\Druid\Tasks;

use Level23\Druid\Context\TaskContext;
use Level23\Druid\Transforms\TransformSpec;
use Level23\Druid\TuningConfig\TuningConfig;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\InputSources\InputSourceInterface;
use Level23\Druid\Granularities\GranularityInterface;

class IndexTask implements TaskInterface
{
    /**
     * @var \Level23\Druid\TuningConfig\TuningConfig|null
     */
    protected $tuningConfig;

    /**
     * @var \Level23\Druid\Context\TaskContext|null
     */
    protected $context;

    /**
     * @var bool
     */
    protected $appendToExisting = false;

    /**
     * @var \Level23\Druid\InputSources\InputSourceInterface
     */
    protected $inputSource;

    /**
     * @var string
     */
    protected $dateSource;

    /**
     * @var \Level23\Druid\Granularities\GranularityInterface
     */
    protected $granularity;

    /**
     * @var \Level23\Druid\Transforms\TransformSpec|null
     */
    protected $transformSpec;

    /**
     * @var \Level23\Druid\Collections\AggregationCollection|null
     */
    protected $aggregations;

    /**
     * @var array
     */
    protected $dimensions;

    /**
     * Whether or not this task should be executed parallel.
     *
     * @var bool
     */
    protected $parallel = false;

    /**
     * @var string|null
     */
    protected $taskId;

    /**
     * IndexTask constructor.
     *
     *
     * @param string                                                $dateSource
     * @param \Level23\Druid\InputSources\InputSourceInterface      $inputSource
     * @param \Level23\Druid\Granularities\GranularityInterface     $granularity
     * @param \Level23\Druid\Transforms\TransformSpec|null          $transformSpec
     * @param \Level23\Druid\TuningConfig\TuningConfig|null         $tuningConfig
     * @param \Level23\Druid\Context\TaskContext|null               $context
     * @param \Level23\Druid\Collections\AggregationCollection|null $aggregations
     * @param array                                                 $dimensions
     * @param string|null                                           $taskId
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
        string $taskId = null
    ) {
        $this->tuningConfig  = $tuningConfig;
        $this->context       = $context;
        $this->inputSource   = $inputSource;
        $this->dateSource    = $dateSource;
        $this->granularity   = $granularity;
        $this->transformSpec = $transformSpec;
        $this->aggregations  = $aggregations;
        $this->dimensions    = $dimensions;
        $this->taskId        = $taskId;
    }

    /**
     * Return the task in a format so that we can send it to druid.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'type' => $this->parallel ? 'index_parallel' : 'index',
            'spec' => [
                'dataSchema' => [
                    'dataSource'      => $this->dateSource,
                    'timestampSpec'   => [
                        'column' => '__time',
                        'format' => 'auto',
                    ],
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
                    'inputFormat'      => [
                        'type' => 'json',
                    ],
                    'appendToExisting' => $this->appendToExisting,
                ],
            ],
        ];

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
     * Whether or not this task should be executed parallel.
     *
     * @param bool $parallel
     */
    public function setParallel(bool $parallel): void
    {
        $this->parallel = $parallel;
    }
}