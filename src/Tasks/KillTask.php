<?php
declare(strict_types=1);

namespace Level23\Druid\Tasks;

use Level23\Druid\Interval\Interval;
use Level23\Druid\Context\TaskContext;

class KillTask implements TaskInterface
{
    protected string $dataSource;

    protected ?string $taskId;

    protected Interval $interval;

    protected ?TaskContext $context;

    /**
     * If markAsUnused is true (default is false), the kill task will first mark any segments within the specified
     * interval as unused, before deleting the unused segments within the interval.
     *
     * @var bool
     */
    protected bool $markAsUnused;

    public function __construct(
        string $dataSource,
        Interval $interval,
        ?string $taskId = null,
        ?TaskContext $context = null,
        bool $markAsUnused = false
    ) {
        $this->dataSource   = $dataSource;
        $this->taskId       = $taskId;
        $this->interval     = $interval;
        $this->context      = $context;
        $this->markAsUnused = $markAsUnused;
    }

    /**
     * Return the task in a format so that we can send it to druid.
     *
     * @return array<string,string|bool|array<string,string|int|bool>>
     */
    public function toArray(): array
    {
        $result = [
            'type'         => 'kill',
            'dataSource'   => $this->dataSource,
            'interval'     => $this->interval->getInterval(),
            'markAsUnused' => $this->markAsUnused,
        ];

        if ($this->taskId) {
            $result['id'] = $this->taskId;
        }

        $context = $this->context ? $this->context->toArray() : [];
        if (count($context) > 0) {
            $result['context'] = $context;
        }

        return $result;
    }
}