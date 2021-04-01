<?php
declare(strict_types=1);

namespace Level23\Druid\TuningConfig;

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
    public function __construct(array $properties = [])
    {
        foreach ($properties as $key => $value) {

            $method = 'set' . $key;

            $callable = [$this, $method];
            if (!is_callable($callable)) {
                $this->properties[$key] = $value;
                continue;
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
        return $this->properties;
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
     * Used to give a hint to control the amount of data that each first phase task reads.
     * This hint could be ignored depending on the implementation of the input source. See Split hint spec for more
     * details.
     *
     * @see https://druid.apache.org/docs/0.20.2/ingestion/native-batch.html#split-hint-spec
     *
     * @param array $splitHintSpec
     *
     * @return \Level23\Druid\TuningConfig\TuningConfig
     */
    public function setSplitHintSpec(array $splitHintSpec)
    {
        $this->properties['splitHintSpec'] = $splitHintSpec;

        return $this;
    }

    /**
     * Defines how to partition data in each timeChunk, see PartitionsSpec
     *
     * @see https://druid.apache.org/docs/0.20.2/ingestion/native-batch.html#partitionsspec
     *
     * @param array $partitionsSpec
     *
     * @return $this
     */
    public function setPartitionsSpec(array $partitionsSpec)
    {
        $this->properties['partitionsSpec'] = $partitionsSpec;

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
     * Defines segment storage format options to be used at indexing time for intermediate persisted temporary segments.
     * This can be used to disable dimension/metric compression on intermediate segments to reduce memory required for
     * final merging. however, disabling compression on intermediate segments might increase page cache use while they
     * are used before getting merged into final segment published, see IndexSpec for possible values.
     *
     * @see https://druid.apache.org/docs/0.20.2/ingestion/index.html#indexspec
     *
     * @param array $indexSpecForIntermediatePersists
     *
     * @return $this
     */
    public function setIndexSpecForIntermediatePersists(array $indexSpecForIntermediatePersists)
    {
        $this->properties['indexSpecForIntermediatePersists'] = $indexSpecForIntermediatePersists;

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
     * Forces guaranteeing the perfect rollup. The perfect rollup optimizes the total size of generated segments and
     * querying time while indexing time will be increased. If this is set to true, intervals in granularitySpec must
     * be set and hashed or single_dim must be used for partitionsSpec. This flag cannot be used with appendToExisting
     * of IOConfig.
     *
     * @param bool $forceGuaranteedRollup
     *
     * @return $this
     */
    public function setForceGuaranteedRollup(bool $forceGuaranteedRollup)
    {
        $this->properties['forceGuaranteedRollup'] = $forceGuaranteedRollup;

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
     * Maximum number of worker tasks which can be run in parallel at the same time. The supervisor task would spawn
     * worker tasks up to maxNumConcurrentSubTasks regardless of the current available task slots.
     * If this value is set to 1, the supervisor task processes data ingestion on its own instead of
     * spawning worker tasks. If this value is set to too large, too many worker tasks can be created which might
     * block other ingestion. Check Capacity Planning for more details.
     *
     * @param int $maxNumConcurrentSubTasks
     *
     * @return $this
     */
    public function setMaxNumConcurrentSubTasks(int $maxNumConcurrentSubTasks)
    {
        $this->properties['maxNumConcurrentSubTasks'] = $maxNumConcurrentSubTasks;

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
     * Max limit for the number of segments that a single task can merge at the same time in the second phase.
     * Used only forceGuaranteedRollup is set.
     *
     * @param int $maxNumSegmentsToMerge
     *
     * @return $this
     */
    public function setMaxNumSegmentsToMerge(int $maxNumSegmentsToMerge)
    {
        $this->properties['maxNumSegmentsToMerge'] = $maxNumSegmentsToMerge;

        return $this;
    }

    /**
     * Total number of tasks to merge segments in the merge phase when partitionsSpec is set to hashed or single_dim.
     *
     * @param int $totalNumMergeTasks
     *
     * @return $this
     */
    public function setTotalNumMergeTasks(int $totalNumMergeTasks)
    {
        $this->properties['totalNumMergeTasks'] = $totalNumMergeTasks;

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