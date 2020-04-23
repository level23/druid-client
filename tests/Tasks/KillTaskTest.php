<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Tasks;

use tests\Level23\Druid\TestCase;
use Level23\Druid\Tasks\KillTask;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Context\TaskContext;

class KillTaskTest extends TestCase
{
    /**
     * @testWith [true, null]
     *           [false, "task-1337"]
     *
     * @param bool        $withContext
     * @param string|null $taskId
     *
     * @throws \Exception
     */
    public function testTask(bool $withContext, string $taskId = null)
    {
        $dataSource = 'myPets';
        $interval   = new Interval('12-02-2019', '13-02-2019');

        $context = $withContext ? new TaskContext(['priority' => 75]) : null;

        $killTask = new KillTask($dataSource, $interval, $taskId, $context);

        $expected = [
            'type'       => 'kill',
            'dataSource' => $dataSource,
            'interval'   => $interval->getInterval(),
        ];
        if (!empty($taskId)) {
            $expected['id'] = $taskId;
        }

        if ($context) {
            $expected['context'] = $context->toArray();
        } else {
            $this->assertArrayNotHasKey('context', $killTask->toArray());
        }

        $this->assertEquals($expected, $killTask->toArray());
    }
}
