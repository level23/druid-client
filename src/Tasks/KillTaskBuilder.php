<?php
declare(strict_types=1);

namespace Level23\Druid\Tasks;

use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Context\TaskContext;
use Level23\Druid\Concerns\HasInterval;

/**
 * Class KillTaskBuilder
 *
 * This class allows you to build a kill task. A kill task will delete all unused segments
 * between the given intervals.
 *
 * @package Level23\Druid\Tasks
 */
class KillTaskBuilder extends TaskBuilder
{
    use HasInterval;

    /**
     * @var string
     */
    protected string $dataSource;

    /**
     * If markAsUnused is true (default is false), the kill task will first mark any segments within the specified
     * interval as unused, before deleting the unused segments within the interval.
     *
     * @var bool
     */
    protected bool $markAsUnused = false;

    /**
     * IndexTaskBuilder constructor.
     *
     * @param \Level23\Druid\DruidClient $druidClient
     * @param string                     $dataSource Data source where you want to delete unused segments for.
     */
    public function __construct(DruidClient $druidClient, string $dataSource)
    {
        $this->client     = $druidClient;
        $this->dataSource = $dataSource;
    }

    /**
     * If markAsUnused is true, the kill task will first mark any segments within the specified
     * interval as unused, before deleting the unused segments within the interval.
     *
     * When calling this method, you can set the `markAsUnused` property. If no parameter specified, we will set it
     * to true. The default setting for markAsUnused is false.
     *
     * @param bool $markAsUnused
     *
     * @return $this
     */
    public function markAsUnused(bool $markAsUnused = true): KillTaskBuilder
    {
        $this->markAsUnused = $markAsUnused;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function buildTask(array|TaskContext $context): TaskInterface
    {
        if ($this->interval === null) {
            throw new InvalidArgumentException('You have to specify an interval!');
        }

        if (!$context instanceof TaskContext) {
            $context = new TaskContext($context);
        }

        return new KillTask($this->dataSource, $this->interval, $this->taskId, $context, $this->markAsUnused);
    }
}