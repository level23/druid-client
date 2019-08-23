<?php
declare(strict_types=1);

namespace Level23\Druid\Tasks;

use Level23\Druid\Interval\Interval;
use Level23\Druid\Context\TaskContext;

class KillTask implements TaskInterface
{
    /**
     * @var string
     */
    protected $dataSource;

    /**
     * @var string
     */
    protected $taskId;

    /**
     * @var \Level23\Druid\Interval\Interval
     */
    protected $interval;

    /**
     * @var \Level23\Druid\Context\TaskContext|null
     */
    protected $context;

    public function __construct(string $dataSource, string $taskId, Interval $interval, TaskContext $context = null)
    {
        $this->dataSource = $dataSource;
        $this->taskId     = $taskId;
        $this->interval   = $interval;
        $this->context    = $context;
    }

    /**
     * Return the task in a format so that we can send it to druid.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'       => 'kill',
            'id'         => $this->taskId,
            'dataSource' => $this->dataSource,
            'interval'   => $this->interval->getInterval(),
            'context'    => ($this->context ? $this->context->toArray() : null),
        ];
    }
}