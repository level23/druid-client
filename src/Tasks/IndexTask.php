<?php
declare(strict_types=1);

namespace Level23\Druid\Tasks;

use Level23\Druid\Context\TaskContext;
use Level23\Druid\Granularities\GranularityInterface;
use Level23\Druid\Transforms\TransformSpec;
use Level23\Druid\TuningConfig\TuningConfig;

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
     * @var \Level23\Druid\Tasks\FirehoseInterface
     */
    protected $firehose;

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
     * IndexTask constructor.
     *
     *
     * @param string                                            $dateSource
     * @param \Level23\Druid\Tasks\FirehoseInterface            $firehose
     * @param \Level23\Druid\Granularities\GranularityInterface $granularity
     * @param \Level23\Druid\Transforms\TransformSpec|null      $transformSpec
     * @param \Level23\Druid\TuningConfig\TuningConfig|null     $tuningConfig
     * @param \Level23\Druid\Context\TaskContext|null           $context
     */
    public function __construct(
        string $dateSource,
        FirehoseInterface $firehose,
        GranularityInterface $granularity,
        TransformSpec $transformSpec = null,
        TuningConfig $tuningConfig = null,
        TaskContext $context = null
    ) {
        $this->tuningConfig  = $tuningConfig;
        $this->context       = $context;
        $this->firehose      = $firehose;
        $this->dateSource    = $dateSource;
        $this->granularity   = $granularity;
        $this->transformSpec = $transformSpec;
    }

    /**
     * Return the task in a format so that we can send it to druid.
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [
            'type' => 'index',
            'spec' => [
                'dataSchema' => [
                    'dataSource'      => $this->dateSource,
                    'parser'          => [
                        'type'      => 'string',
                        'parseSpec' => [
                            'format'        => 'json',
                            'timestampSpec' => [
                                'column' => '__time',
                                'format' => 'auto',
                            ],
                        ],
                    ],
                    'metricSpec'      => [],
                    'granularitySpec' => $this->granularity->toArray(),
                    'transformSpec'   => ($this->transformSpec ? $this->transformSpec->toArray() : null),
                ],
                'ioConfig'   => [
                    'type'             => 'index',
                    'firehose'         => $this->firehose->toArray(),
                    'appendToExisting' => $this->appendToExisting,
                ],

            ],
        ];

        if ($this->tuningConfig) {
            $this->tuningConfig->type       = 'index';
            $result['spec']['tuningConfig'] = $this->tuningConfig->toArray();
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
}