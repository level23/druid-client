<?php
declare(strict_types=1);

namespace Level23\Druid\Tasks;

use InvalidArgumentException;
use Level23\Druid\DruidClient;
use Level23\Druid\Interval\IntervalInterface;

abstract class TaskBuilder
{
    /**
     * @var DruidClient
     */
    protected $client;

    /**
     * Check if the given interval is valid for the given dataSource.
     *
     * @param string                                    $dataSource
     * @param \Level23\Druid\Interval\IntervalInterface $interval
     *
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    protected function validateInterval(string $dataSource, IntervalInterface $interval): void
    {
        $fromStr = $interval->getStart()->format('Y-m-d\TH:i:s.000\Z');
        $toStr   = $interval->getStop()->format('Y-m-d\TH:i:s.000\Z');

        $foundFrom = false;
        $foundTo   = false;

        // Get all intervals and check if our interval is among them.
        $intervals = array_keys($this->client->metadata()->intervals($dataSource));

        foreach ($intervals as $dateStr) {

            if (!$foundFrom && substr($dateStr, 0, strlen($fromStr)) === $fromStr) {
                $foundFrom = true;
            }

            if (!$foundTo && substr($dateStr, -strlen($toStr)) === $toStr) {
                $foundTo = true;
            }

            if ($foundFrom && $foundTo) {
                return;
            }
        }

        throw new InvalidArgumentException(
            'Error, invalid interval given. The given dates do not match a complete interval!' . PHP_EOL .
            'Given interval: ' . $interval->getInterval() . PHP_EOL .
            'Valid intervals: ' . implode(', ', $intervals)
        );
    }

    /**
     * Execute the index task. We will return the task identifier.
     *
     * @param \Level23\Druid\Context\TaskContext|array $context
     *
     * @return string
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function execute($context = [])
    {
        $task = $this->buildTask($context);

        return $this->client->executeTask($task);
    }

    /**
     * Return the task in Json format.
     *
     * @param \Level23\Druid\Context\TaskContext|array $context
     *
     * @return string
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function toJson($context = []): string
    {
        $task = $this->buildTask($context);

        $json = \GuzzleHttp\json_encode($task->toArray(), JSON_PRETTY_PRINT);

        return $json;
    }

    /**
     * Return the task as array
     *
     * @param \Level23\Druid\Context\TaskContext|array $context
     *
     * @return array
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    public function toArray($context = []): array
    {
        $task = $this->buildTask($context);

        return $task->toArray();
    }

    /**
     * @param \Level23\Druid\Context\TaskContext|array $context
     *
     * @return \Level23\Druid\Tasks\TaskInterface
     * @throws \Level23\Druid\Exceptions\QueryResponseException
     */
    abstract protected function buildTask($context): TaskInterface;
}