<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Tasks;

use tests\TestCase;
use InvalidArgumentException;
use Level23\Druid\Tasks\CompactTask;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Context\TaskContext;
use Level23\Druid\TuningConfig\TuningConfig;

class CompactTaskTest extends TestCase
{
    /**
     * @testWith ["day", null, null, null]
     *           ["week", {"maxRowsPerSegment": "1"}, null, null]
     *           ["week", {"wrong": "index"}, null, null, true]
     *           ["week", {"type": "index"}, {"priority": 10}, null]
     *           ["week", {"type": "index"}, {"wrong": 10}, null, true]
     *           ["week", {"type": "index"}, null, 1024]
     *
     *
     * @param string     $segmentGranularity
     * @param array|null $tuningConfig
     * @param array|null $context
     * @param int|null   $targetCompactionSizeBytes
     * @param bool       $expectException
     *
     * @throws \Exception
     */
    public function testCompactTask(
        string $segmentGranularity,
        array $tuningConfig = null,
        array $context = null,
        int $targetCompactionSizeBytes = null,
        bool $expectException = false
    ) {
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }

        $interval = new Interval('20-02-2019', '22-02-2019');

        $task = new CompactTask(
            'mySource',
            $interval,
            $segmentGranularity,
            ($tuningConfig ? new TuningConfig($tuningConfig) : null),
            ($context ? new TaskContext($context) : null),
            $targetCompactionSizeBytes
        );

        $expected = [
            'type'       => 'compact',
            'dataSource' => 'mySource',
            'interval'   => $interval->getInterval(),
        ];

        if ($segmentGranularity) {
            $expected['segmentGranularity'] = $segmentGranularity;
        }

        if ($targetCompactionSizeBytes) {
            $expected['targetCompactionSizeBytes'] = $targetCompactionSizeBytes;
        }

        if ($tuningConfig) {
            $expected['tuningConfig']         = $tuningConfig;
            $expected['tuningConfig']['type'] = 'index';
        }
        if (is_array($context) && count($context) > 0) {
            $contextObj          = new TaskContext($context);
            $expected['context'] = $contextObj->toArray();
        } else {
            $this->assertArrayNotHasKey('context', $task->toArray());
        }

        $this->assertEquals($expected, $task->toArray());
    }
}