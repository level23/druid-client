<?php
declare(strict_types=1);

namespace Level23\Druid\TuningConfig;

class TuningConfig implements TuningConfigInterface
{
    /**
     * @var array<string,string|int|bool|array<string,string|int>>
     */
    protected array $properties = [];

    /**
     * TuningConfig constructor.
     *
     * @param array<string,string|int|bool|array<string,string|int>> $properties
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
     * @return array<string,string|int|bool|array<string,string|int>>
     */
    public function toArray(): array
    {
        return $this->properties;
    }

    /**
     * The task type
     *
     * @param string $type
     *
     * @return $this
     * @required
     */
    public function setType(string $type): self
    {
        $this->properties['type'] = $type;

        return $this;
    }

    /**
     * Used in sharding. Determines how many rows are in each segment.
     * Default: 5000000
     *
     * @param int $maxRowsPerSegment
     *
     * @return $this
     */
    public function setMaxRowsPerSegment(int $maxRowsPerSegment): self
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
     * @param int $maxRowsInMemory
     *
     * @return $this
     */
    public function setMaxRowsInMemory(int $maxRowsInMemory): self
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
     * @param int $maxBytesInMemory
     *
     * @return $this
     */
    public function setMaxBytesInMemory(int $maxBytesInMemory): self
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
     * @param int $maxTotalRows
     *
     * @return $this
     */
    public function setMaxTotalRows(int $maxTotalRows): self
    {
        $this->properties['maxTotalRows'] = $maxTotalRows;

        return $this;
    }

    /**
     * Directly specify the number of shards to create. If this is specified and 'intervals' is specified in the
     * granularitySpec, the index task can skip to determine intervals/partitions pass through the data. numShards
     * cannot be specified if maxRowsPerSegment is set.
     *
     * Default: null
     *
     * @param int $numShards
     *
     * @return $this
     */
    public function setNumShards(int $numShards): self
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
     * @param array<string,string|int> $splitHintSpec
     *
     * @return \Level23\Druid\TuningConfig\TuningConfig
     */
    public function setSplitHintSpec(array $splitHintSpec): self
    {
        $this->properties['splitHintSpec'] = $splitHintSpec;

        return $this;
    }

    /**
     * Defines how to partition data in each timeChunk, see PartitionsSpec
     *
     * @see https://druid.apache.org/docs/0.20.2/ingestion/native-batch.html#partitionsspec
     *
     * @param array<string,string|int> $partitionsSpec
     *
     * @return $this
     */
    public function setPartitionsSpec(array $partitionsSpec): self
    {
        $this->properties['partitionsSpec'] = $partitionsSpec;

        return $this;
    }

    /**
     * Defines segment storage format options to be used at indexing time
     *
     * @see https://druid.apache.org/docs/latest/ingestion/native_tasks.html#indexspec
     *
     * @param array<string,string|int> $indexSpec
     *
     * @return $this
     */
    public function setIndexSpec(array $indexSpec): self
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
     * @param array<string,string|int> $indexSpecForIntermediatePersists
     *
     * @return $this
     */
    public function setIndexSpecForIntermediatePersists(array $indexSpecForIntermediatePersists): self
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
     * @param int $maxPendingPersists
     *
     * @return $this
     */
    public function setMaxPendingPersists(int $maxPendingPersists): self
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
     * @param bool $reportParseExceptions
     *
     * @return $this
     */
    public function setReportParseExceptions(bool $reportParseExceptions): self
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
    public function setForceGuaranteedRollup(bool $forceGuaranteedRollup): self
    {
        $this->properties['forceGuaranteedRollup'] = $forceGuaranteedRollup;

        return $this;
    }

    /**
     * Milliseconds to wait for pushing segments. It must be >= 0, where 0 means to wait forever.
     *
     * Default: 0
     *
     * @param int $pushTimeout
     *
     * @return $this
     */
    public function setPushTimeout(int $pushTimeout): self
    {
        $this->properties['pushTimeout'] = $pushTimeout;

        return $this;
    }

    /**
     * Segment write-out medium to use when creating segments. See SegmentWriteOutMediumFactory.
     *
     * Default: Not specified, the value from druid.peon.defaultSegmentWriteOutMediumFactory.type is used
     *
     * @param string $segmentWriteOutMediumFactory
     *
     * @return $this
     */
    public function setSegmentWriteOutMediumFactory(string $segmentWriteOutMediumFactory): self
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
    public function setMaxNumConcurrentSubTasks(int $maxNumConcurrentSubTasks): self
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
     * @param int $maxNumSubTasks
     *
     * @return $this
     */
    public function setMaxNumSubTasks(int $maxNumSubTasks): self
    {
        $this->properties['maxNumSubTasks'] = $maxNumSubTasks;

        return $this;
    }

    /**
     * Maximum number of retries on task failures.
     *
     * Default: 3
     *
     * @param int $maxRetry
     *
     * @return $this
     */
    public function setMaxRetry(int $maxRetry): self
    {
        $this->properties['maxRetry'] = $maxRetry;

        return $this;
    }

    /**
     * Maximum limit for the number of segments that a single task can merge at the same time in the second phase.
     * Used only forceGuaranteedRollup is set.
     *
     * @param int $maxNumSegmentsToMerge
     *
     * @return $this
     */
    public function setMaxNumSegmentsToMerge(int $maxNumSegmentsToMerge): self
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
    public function setTotalNumMergeTasks(int $totalNumMergeTasks): self
    {
        $this->properties['totalNumMergeTasks'] = $totalNumMergeTasks;

        return $this;
    }

    /**
     * Polling period in milliseconds to check running task statuses.
     *
     * Default: 1000
     *
     * @param int $taskStatusCheckPeriodMs
     *
     * @return $this
     */
    public function setTaskStatusCheckPeriodMs(int $taskStatusCheckPeriodMs): self
    {
        $this->properties['taskStatusCheckPeriodMs'] = $taskStatusCheckPeriodMs;

        return $this;
    }

    /**
     * Timeout for reporting the pushed segments in worker tasks.
     *
     * Default: PT10S
     *
     * @param string $chatHandlerTimeout
     *
     * @return $this
     */
    public function setChatHandlerTimeout(string $chatHandlerTimeout): self
    {
        $this->properties['chatHandlerTimeout'] = $chatHandlerTimeout;

        return $this;
    }

    /**
     * Retries for reporting the pushed segments in worker tasks.
     *
     * Default: 5
     *
     * @param int $chatHandlerNumRetries
     *
     * @return $this
     */
    public function setChatHandlerNumRetries(int $chatHandlerNumRetries): self
    {
        $this->properties['chatHandlerNumRetries'] = $chatHandlerNumRetries;

        return $this;
    }
}