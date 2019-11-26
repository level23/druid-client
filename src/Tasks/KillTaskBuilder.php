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
    protected $dataSource;

    /**
     * IndexTaskBuilder constructor.
     *
     * @param \Level23\Druid\DruidClient $client
     * @param string                     $dataSource Data source where you want to delete unused segments for.
     */
    public function __construct(DruidClient $client, string $dataSource)
    {
        $this->client     = $client;
        $this->dataSource = $dataSource;
    }

    /**
     * @inheritDoc
     */
    protected function buildTask($context): TaskInterface
    {
        if ($this->interval === null) {
            throw new InvalidArgumentException('You have to specify an interval!');
        }

        if (!$context instanceof TaskContext) {
            $context = new TaskContext($context);
        }

        return new KillTask($this->dataSource, $this->interval, $this->taskId, $context);
    }
}