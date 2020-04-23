<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Tasks;

use tests\Level23\Druid\TestCase;
use Level23\Druid\Tasks\IndexTask;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Context\TaskContext;
use Level23\Druid\Transforms\TransformSpec;
use Level23\Druid\TuningConfig\TuningConfig;
use Level23\Druid\Aggregations\SumAggregator;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Transforms\ExpressionTransform;
use Level23\Druid\Firehoses\IngestSegmentFirehose;
use Level23\Druid\Collections\TransformCollection;
use Level23\Druid\Granularities\UniformGranularity;
use Level23\Druid\Collections\AggregationCollection;

class IndexTaskTest extends TestCase
{
    /**
     * @testWith [true, true, true, true, true, null]
     *           [false, false, false, false, false, "task-1337"]
     *
     * @param bool $withAggregations
     * @param bool $withTransformSpec
     * @param bool $withAppend
     * @param bool $withTuning
     * @param bool $withContext
     * @param string|null $taskId
     *
     * @throws \ReflectionException
     */
    public function testTask(
        bool $withAggregations,
        bool $withTransformSpec,
        bool $withAppend,
        bool $withTuning,
        bool $withContext,
        string $taskId = null
    ) {
        $dataSource = 'people';
        $interval   = new Interval('12-02-2019', '13-02-2019');

        $firehose = new IngestSegmentFirehose($dataSource, $interval);

        $granularity = new UniformGranularity(
            'week',
            'day',
            true,
            new IntervalCollection($interval)
        );

        $transformSpec = $withTransformSpec ? new TransformSpec(
            new TransformCollection(new ExpressionTransform('concat(foo, bar)', 'fooBar')),
            null
        ) : null;

        $tuningConfig = $withTuning ? new TuningConfig(['maxRowsInMemory' => 5000]) : null;

        $taskContext = $withContext ? new TaskContext(['priority' => 75]) : null;

        $aggregations = $withAggregations ? new AggregationCollection(
            new SumAggregator('age')
        ) : null;

        $dimensions = [
            ['name' => 'country', 'type' => 'string'],
            ['name' => 'duration', 'type' => 'long'],
        ];

        $task = new IndexTask(
            $dataSource,
            $firehose,
            $granularity,
            $transformSpec,
            $tuningConfig,
            $taskContext,
            $aggregations,
            $dimensions,
            $taskId
        );

        $this->assertFalse($this->getProperty($task, 'appendToExisting'));

        $task->setAppendToExisting($withAppend);

        $expected = [
            'type' => 'index',
            'spec' => [
                'dataSchema' => [
                    'dataSource'      => $dataSource,
                    'parser'          => [
                        'type'      => 'string',
                        'parseSpec' => [
                            'format'         => 'json',
                            'timestampSpec'  => [
                                'column' => '__time',
                                'format' => 'auto',
                            ],
                            'dimensionsSpec' => [
                                'dimensions' => $dimensions,
                            ],
                        ],
                    ],
                    'metricsSpec'     => ($aggregations ? $aggregations->toArray() : null),
                    'granularitySpec' => $granularity->toArray(),
                    'transformSpec'   => ($transformSpec ? $transformSpec->toArray() : null),
                ],
                'ioConfig'   => [
                    'type'             => 'index',
                    'firehose'         => $firehose->toArray(),
                    'appendToExisting' => $withAppend,
                ],
            ],
        ];

        if( $taskId ) {
            $expected['id'] = $taskId;
        }

        if ($taskContext instanceof TaskContext) {
            $expected['context'] = $taskContext->toArray();
        } else {
            $this->assertArrayNotHasKey('context', $task->toArray());
        }

        if ($tuningConfig instanceof TuningConfig) {
            $expected['spec']['tuningConfig'] = [
                'type'            => 'index',
                'maxRowsInMemory' => 5000,
            ];
        }

        $this->assertEquals($expected, $task->toArray());

        $task->setParallel(true);

        if ($tuningConfig instanceof TuningConfig) {
            $expected['spec']['tuningConfig']['type'] = 'index_parallel';
        }

        $expected['spec']['ioConfig']['type'] = 'index_parallel';
        $expected['type'] = 'index_parallel';

        $this->assertEquals($expected, $task->toArray());
    }
}
