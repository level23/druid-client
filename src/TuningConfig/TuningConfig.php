<?php
declare(strict_types=1);

namespace Level23\Druid\TuningConfig;

use InvalidArgumentException;

class TuningConfig implements TuningConfigInterface
{
    /**
     * The task type
     *
     * @var string
     * @required
     */
    public $type;

    /**
     * Used in sharding. Determines how many rows are in each segment.
     * Default: 5000000
     *
     * @var int
     */
    public $maxRowsPerSegment;

    /**
     * Used in determining when intermediate persists to disk should occur. Normally user does not need to set this,
     * but depending on the nature of data, if rows are short in terms of bytes, user may not want to store a million
     * rows in memory and this value should be set.
     *
     * Default: 1000000
     *
     * @var int
     */
    public $maxRowsInMemory;

    /**
     * Used in determining when intermediate persists to disk should occur. Normally this is computed internally and
     * user does not need to set it. This value represents number of bytes to aggregate in heap memory before
     * persisting. This is based on a rough estimate of memory usage and not actual usage. The maximum heap memory
     * usage for indexing is maxBytesInMemory * (2 + maxPendingPersists)
     *
     * Default: 1/6 of max JVM memory
     *
     * @var int
     */
    public $maxBytesInMemory;

    /**
     * Total number of rows in segments waiting for being pushed. Used in determining when intermediate pushing should
     * occur.
     *
     * Default: 20000000
     *
     * @var int
     */
    public $maxTotalRows;

    /**
     * Directly specify the number of shards to create. If this is specified and 'intervals' is specified in the
     * granularitySpec, the index task can skip the determine intervals/partitions pass through the data. numShards
     * cannot be specified if maxRowsPerSegment is set.
     *
     * Default: null
     *
     * @var int
     */
    public $numShards;

    /**
     * Defines segment storage format options to be used at indexing time
     *
     * @see https://druid.apache.org/docs/latest/ingestion/native_tasks.html#indexspec
     *
     * @var array
     */
    public $indexSpec;

    /**
     * Maximum number of persists that can be pending but not started. If this limit would be exceeded by a new
     * intermediate persist, ingestion will block until the currently-running persist finishes. Maximum heap memory
     * usage for indexing scales with maxRowsInMemory * (2 + maxPendingPersists).
     *
     * Default: 0 (meaning one persist can be running concurrently with ingestion, and none can be queued up)
     *
     * @var int
     */
    public $maxPendingPersists;

    /**
     * If true, exceptions encountered during parsing will be thrown and will halt ingestion; if false, unparseable
     * rows and fields will be skipped.
     *
     * Default: false
     *
     * @var bool
     */
    public $reportParseExceptions;

    /**
     * Milliseconds to wait for pushing segments. It must be >= 0, where 0 means to wait forever.
     *
     * Default: 0
     *
     * @var int
     */
    public $pushTimeout;

    /**
     * Segment write-out medium to use when creating segments. See SegmentWriteOutMediumFactory.
     *
     * Default: Not specified, the value from druid.peon.defaultSegmentWriteOutMediumFactory.type is used
     *
     * @var
     */
    public $segmentWriteOutMediumFactory;

    /**
     * Maximum number of tasks which can be run at the same time. The supervisor task would spawn worker tasks up to
     * maxNumSubTasks regardless of the available task slots. If this value is set to 1, the supervisor task processes
     * data ingestion on its own instead of spawning worker tasks. If this value is set to too large, too many worker
     * tasks can be created which might block other ingestion. Check Capacity Planning for more details.
     *
     * Default: 1
     *
     * @var int
     */
    public $maxNumSubTasks;

    /**
     * Maximum number of retries on task failures.
     *
     * Default: 3
     *
     * @var int
     */
    public $maxRetry;

    /**
     * Polling period in milliseconds to check running task statuses.
     *
     * Default: 1000
     *
     * @var int
     */
    public $taskStatusCheckPeriodMs;

    /**
     * Timeout for reporting the pushed segments in worker tasks.
     *
     * Default: PT10S
     *
     * @var string
     */
    public $chatHandlerTimeout;

    /**
     * Retries for reporting the pushed segments in worker tasks.
     *
     * Default: 5
     *
     * @var int
     */
    public $chatHandlerNumRetries;

    /**
     * Return the tuning config as it can be used in the druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        $properties = get_object_vars($this);

        $result = [];
        foreach ($properties as $property => $value) {
            if ($value !== null) {
                $result[$property] = $value;
            }
        }

        return $result;
    }

    /**
     * TuningConfig constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $key => $value) {

            if (!property_exists($this, $key)) {
                throw new InvalidArgumentException(
                    'Setting ' . $key . ' was not found in ' . __CLASS__
                );
            }

            if (!is_scalar($value)) {
                throw new InvalidArgumentException(
                    'Invalid value ' . var_export($value, true) .
                    ' for ' . $key . ' in ' . __CLASS__
                );
            }

            $this->$key = $value;
        }
    }
}