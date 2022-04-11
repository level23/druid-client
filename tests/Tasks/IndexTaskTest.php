<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Tasks;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Tasks\IndexTask;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Context\TaskContext;
use Level23\Druid\Transforms\TransformSpec;
use Level23\Druid\Dimensions\TimestampSpec;
use Level23\Druid\TuningConfig\TuningConfig;
use Level23\Druid\Aggregations\SumAggregator;
use Level23\Druid\InputFormats\CsvInputFormat;
use Level23\Druid\Dimensions\SpatialDimension;
use Level23\Druid\InputSources\HttpInputSource;
use Level23\Druid\InputFormats\JsonInputFormat;
use Level23\Druid\InputSources\DruidInputSource;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Transforms\ExpressionTransform;
use Level23\Druid\Collections\TransformCollection;
use Level23\Druid\Granularities\UniformGranularity;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Collections\SpatialDimensionCollection;

class IndexTaskTest extends TestCase
{
    /**
     * @testWith [true, true, true, true, true, true, null]
     *           [false, false, false, false, false, false, "task-1337"]
     *
     * @param bool        $withAggregations
     * @param bool        $withTransformSpec
     * @param bool        $withAppend
     * @param bool        $withTuning
     * @param bool        $withContext
     * @param bool        $withSpatialDimensions
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
        bool $withSpatialDimensions,
        string $taskId = null
    ): void {
        $dataSource = 'people';
        $interval   = new Interval('12-02-2019', '13-02-2019');

        $inputSource = new DruidInputSource($dataSource, $interval);

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

        $spatialDimensions = $withSpatialDimensions ? new SpatialDimensionCollection(new SpatialDimension(
            'location', ['lat', 'long']
        )) : null;

        $aggregations = $withAggregations ? new AggregationCollection(
            new SumAggregator('age')
        ) : null;

        $dimensions = [
            ['name' => 'country', 'type' => 'string'],
            ['name' => 'duration', 'type' => 'long'],
        ];

        $inputFormat = new JsonInputFormat();

        $task = new IndexTask(
            $dataSource,
            $inputSource,
            $granularity,
            $transformSpec,
            $tuningConfig,
            $taskContext,
            $aggregations,
            $dimensions,
            $taskId,
            $inputFormat,
            null,
            $spatialDimensions
        );

        $timestampSpec = new TimestampSpec('timestamp', 'auto');
        $task->setTimestampSpec($timestampSpec);

        $this->assertFalse($this->getProperty($task, 'appendToExisting'));
        $this->assertEquals($inputFormat, $this->getProperty($task, 'inputFormat'));

        $inputFormat = new CsvInputFormat(['name', 'age']);
        $task->setInputFormat($inputFormat);
        $this->assertEquals($inputFormat, $this->getProperty($task, 'inputFormat'));

        $task->setAppendToExisting($withAppend);

        $expected = [
            'type' => 'index',
            'spec' => [
                'dataSchema' => [
                    'dataSource'     => $dataSource,
                    'timestampSpec'  => $timestampSpec->toArray(),
                    'dimensionsSpec' => [
                        'dimensions' => $dimensions,
                    ],

                    'metricsSpec'     => ($aggregations ? $aggregations->toArray() : null),
                    'granularitySpec' => $granularity->toArray(),
                    'transformSpec'   => ($transformSpec ? $transformSpec->toArray() : null),
                ],
                'ioConfig'   => [
                    'type'             => 'index',
                    'inputSource'      => $inputSource->toArray(),
                    'inputFormat'      => $inputFormat->toArray(),
                    'appendToExisting' => $withAppend,
                ],
            ],
        ];

        if ($spatialDimensions) {
            $expected['spec']['dataSchema']['dimensionsSpec']['spatialDimensions'] = $spatialDimensions->toArray();
        }

        if ($taskId) {
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
        $expected['type']                     = 'index_parallel';

        $this->assertEquals($expected, $task->toArray());
    }

    public function testTaskWithoutTimestamp(): void
    {
        $task = new IndexTask(
            'money',
            new HttpInputSource(['http://127.0.0.1/data.json']),
            new UniformGranularity('day', 'day', true, new IntervalCollection(
                new Interval('now - 1 day', 'now')
            ))
        );

        $this->expectExceptionMessage('You have to specify your timestamp column!');
        $this->expectException(\InvalidArgumentException::class);
        $task->toArray();
    }
}
