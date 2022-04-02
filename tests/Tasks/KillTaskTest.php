<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Tasks;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Tasks\KillTask;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Context\TaskContext;

class KillTaskTest extends TestCase
{
    /**
     * @testWith [true, null, false]
     *           [true, null, true]
     *           [true, null, null]
     *           [false, "task-1337", true]
     *           [false, "task-1337", false]
     *           [false, "task-1337", null]
     *
     * @param bool        $withContext
     * @param string|null $taskId
     * @param bool|null   $markAsUnused
     */
    public function testTask(bool $withContext, ?string $taskId, ?bool $markAsUnused): void
    {
        $dataSource = 'myPets';
        $interval   = new Interval('12-02-2019', '13-02-2019');

        $context = $withContext ? new TaskContext(['priority' => 75]) : null;

        if( $markAsUnused === null ) {
            $killTask = new KillTask($dataSource, $interval, $taskId, $context);
        } else {
            $killTask = new KillTask($dataSource, $interval, $taskId, $context, $markAsUnused);
        }

        $expected = [
            'type'       => 'kill',
            'dataSource' => $dataSource,
            'interval'   => $interval->getInterval(),
            'markAsUnused' => $markAsUnused ?? false
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
