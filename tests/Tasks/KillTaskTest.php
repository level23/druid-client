<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Tasks;

use tests\TestCase;
use Level23\Druid\Tasks\KillTask;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Context\TaskContext;

class KillTaskTest extends TestCase
{
    /**
     * @testWith [true]
     *           [false]
     *
     * @param bool $withContext
     *
     * @throws \Exception
     */
    public function testTask(bool $withContext)
    {
        $dataSource = 'myPets';
        $taskId     = 'task-1337';
        $interval   = new Interval('12-02-2019', '13-02-2019');

        $context = $withContext ? new TaskContext(['priority' => 75]) : null;

        $killTask = new KillTask($dataSource, $taskId, $interval, $context);

        $this->assertEquals([
            'type'       => 'kill',
            'id'         => $taskId,
            'dataSource' => $dataSource,
            'interval'   => $interval->getInterval(),
            'context'    => ($context ? $context->toArray() : null),
        ], $killTask->toArray());
    }
}