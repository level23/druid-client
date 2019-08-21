<?php
declare(strict_types=1);

namespace Level23\Druid\TuningConfig;

use InvalidArgumentException;

class TuningConfig implements TuningConfigInterface
{
    /**
     * @var array
     */
    protected $properties = [];

    /**
     * TuningConfig constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        foreach ($properties as $key => $value) {

            $method = 'set' . ucfirst($key);

            $callable = [$this, $method];
            if (!method_exists($this, $method) || !is_callable($callable)) {
                throw new InvalidArgumentException(
                    'Setting ' . $key . ' was not found in ' . __CLASS__
                );
            }

            call_user_func($callable, $value);
        }
    }

    /**
     * Return the context as it can be used in the druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_filter($this->properties, function ($value) {
            return ($value !== null);
        });
    }

    /**
     * The task type
     *
     * @return $this
     * @var string
     * @required
     */
    public function setType(string $type)
    {
        $this->properties['type'] = $type;

        return $this;
    }

    /**
     * Used in sharding. Determines how many rows are in each segment.
     * Default: 5000000
     *
     * @return $this
     * @var int
     */
    public function setMaxRowsPerSegment(int $maxRowsPerSegment)
    {
        $this->properties['maxRowsPerSegment'] = $maxRowsPerSegment;

        return $this;
    }

    /**
     * Used in determining when intermediate persists to disk should occur. Normally user does not need to set this,
     * but depending on the nature of data, if rows are short in terms of bytes, user may not want to store a million
     * rows in memory and this value should be set.
     *
     * Default: 1000000
     *
     * @return $this
     * @var int
     */
    public function setMaxRowsInMemory(int $maxRowsInMemory)
    {
        $this->properties['maxRowsInMemory'] = $maxRowsInMemory;

        return $this;
    }

    /**
     * Used in determining when intermediate persists to disk should occur. Normally this is computed internally and
     * user does not need to set it. This value represents number of bytes to aggregate in heap memory before
     * persisting. This is based on a rough estimate of memory usage and not actual usage. The maximum heap memory
     * usage for indexing is maxBytesInMemory * (2 + maxPendingPersists)
     *
     * Default: 1/6 of max JVM memory
     *
     * @return $this
     * @var int
     */
    public function setMaxBytesInMemory(int $maxBytesInMemory)
    {
        $this->properties['maxBytesInMemory'] = $maxBytesInMemory;

        return $this;
    }

    /**
     * Total number of rows in segments waiting for being pushed. Used in determining when intermediate pushing should
     * occur.
     *
     * Default: 20000000
     *
     * @return $this
     * @var int
     */
    public function setMaxTotalRows(int $maxTotalRows)
    {
        $this->properties['maxTotalRows'] = $maxTotalRows;

        return $this;
    }

    /**
     * Directly specify the number of shards to create. If this is specified and 'intervals' is specified in the
     * granularitySpec, the index task can skip the determine intervals/partitions pass through the data. numShards
     * cannot be specified if maxRowsPerSegment is set.
     *
     * Default: null
     *
     * @return $this
     * @var int
     */
    public function setNumShards(int $numShards)
    {
        $this->properties['numShards'] = $numShards;

        return $this;
    }

    /**
     * Defines segment storage format options to be used at indexing time
     *
     * @see https://druid.apache.org/docs/latest/ingestion/native_tasks.html#indexspec
     *
     * @var array
     * @return $this
     */
    public function setIndexSpec(array $indexSpec)
    {
        $this->properties['indexSpec'] = $indexSpec;

        return $this;
    }

    /**
     * Maximum number of persists that can be pending but not started. If this limit would be exceeded by a new
     * intermediate persist, ingestion will block until the currently-running persist finishes. Maximum heap memory
     * usage for indexing scales with maxRowsInMemory * (2 + maxPendingPersists).
     *
     * Default: 0 (meaning one persist can be running concurrently with ingestion, and none can be queued up)
     *
     * @return $this
     * @var int
     */
    public function setMaxPendingPersists(int $maxPendingPersists)
    {
        $this->properties['maxPendingPersists'] = $maxPendingPersists;

        return $this;
    }

    /**
     * If true, exceptions encountered during parsing will be thrown and will halt ingestion; if false, unparseable
     * rows and fields will be skipped.
     *
     * Default: false
     *
     * @return $this
     * @var bool
     */
    public function setReportParseExceptions(bool $reportParseExceptions)
    {
        $this->properties['reportParseExceptions'] = $reportParseExceptions;

        return $this;
    }

    /**
     * Milliseconds to wait for pushing segments. It must be >= 0, where 0 means to wait forever.
     *
     * Default: 0
     *
     * @return $this
     * @var int
     */
    public function setPushTimeout(int $pushTimeout)
    {
        $this->properties['pushTimeout'] = $pushTimeout;

        return $this;
    }

    /**
     * Segment write-out medium to use when creating segments. See SegmentWriteOutMediumFactory.
     *
     * Default: Not specified, the value from druid.peon.defaultSegmentWriteOutMediumFactory.type is used
     *
     * @return $this
     * @var string
     */
    public function setSegmentWriteOutMediumFactory(string $segmentWriteOutMediumFactory)
    {
        $this->properties['segmentWriteOutMediumFactory'] = $segmentWriteOutMediumFactory;

        return $this;
    }

    /**
     * Maximum number of tasks which can be run at the same time. The supervisor task would spawn worker tasks up to
     * maxNumSubTasks regardless of the available task slots. If this value is set to 1, the supervisor task processes
     * data ingestion on its own instead of spawning worker tasks. If this value is set to too large, too many worker
     * tasks can be created which might block other ingestion. Check Capacity Planning for more details.
     *
     * Default: 1
     *
     * @return $this
     * @var int
     */
    public function setMaxNumSubTasks(int $maxNumSubTasks)
    {
        $this->properties['maxNumSubTasks'] = $maxNumSubTasks;

        return $this;
    }

    /**
     * Maximum number of retries on task failures.
     *
     * Default: 3
     *
     * @return $this
     * @var int
     */
    public function setMaxRetry(int $maxRetry)
    {
        $this->properties['maxRetry'] = $maxRetry;

        return $this;
    }

    /**
     * Polling period in milliseconds to check running task statuses.
     *
     * Default: 1000
     *
     * @return $this
     * @var int
     */
    public function setTaskStatusCheckPeriodMs(int $taskStatusCheckPeriodMs)
    {
        $this->properties['taskStatusCheckPeriodMs'] = $taskStatusCheckPeriodMs;

        return $this;
    }

    /**
     * Timeout for reporting the pushed segments in worker tasks.
     *
     * Default: PT10S
     *
     * @return $this
     * @var string
     */
    public function setChatHandlerTimeout(string $chatHandlerTimeout)
    {
        $this->properties['chatHandlerTimeout'] = $chatHandlerTimeout;

        return $this;
    }

    /**
     * Retries for reporting the pushed segments in worker tasks.
     *
     * Default: 5
     *
     * @return $this
     * @var int
     */
    public function setChatHandlerNumRetries(int $chatHandlerNumRetries)
    {
        $this->properties['chatHandlerNumRetries'] = $chatHandlerNumRetries;

        return $this;
    }
}