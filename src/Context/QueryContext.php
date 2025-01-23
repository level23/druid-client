<?php
declare(strict_types=1);

namespace Level23\Druid\Context;

/**
 * Class QueryContext
 *
 * The query context is used for various query configuration parameters. The following parameters apply to all queries.
 *
 * @package Level23\Druid\Context
 */
class QueryContext extends Context implements ContextInterface
{
    /**
     * Query timeout in millis, beyond which unfinished queries will be cancelled. 0 timeout means no timeout. To set
     * the default timeout, see Broker configuration
     *
     * @param int $timeout
     *
     * @return $this
     */
    public function setTimeout(int $timeout): self
    {
        $this->properties['timeout'] = $timeout;

        return $this;
    }

    /**
     * Query Priority. Queries with higher priority get precedence for computational resources.
     *
     * @param int $priority
     *
     * @return $this
     */
    public function setPriority(int $priority): self
    {
        $this->properties['priority'] = $priority;

        return $this;
    }

    /**
     * Unique identifier given to this query. If a query ID is set or known, this can be used to cancel the query
     *
     * @param string $queryId
     *
     * @return $this
     */
    public function setQueryId(string $queryId): self
    {
        $this->properties['queryId'] = $queryId;

        return $this;
    }

    /**
     * Flag indicating whether to leverage the query cache for this query. When set to false, it disables reading from
     * the query cache for this query. When set to true, Apache Druid (incubating) uses druid.broker.cache.useCache or
     * druid.historical.cache.useCache to determine whether to read from the query cache
     *
     * @param bool $useCache
     *
     * @return $this
     */
    public function setUseCache(bool $useCache): self
    {
        $this->properties['useCache'] = $useCache;

        return $this;
    }

    /**
     * Flag indicating whether to save the results of the query to the query cache. Primarily used for debugging. When
     * set to false, it disables saving the results of this query to the query cache. When set to true, Druid uses
     * druid.broker.cache.populateCache or druid.historical.cache.populateCache to determine whether to save the
     * results of this query to the query cache
     *
     * @param bool $populateCache
     *
     * @return $this
     */
    public function setPopulateCache(bool $populateCache): self
    {
        $this->properties['populateCache'] = $populateCache;

        return $this;
    }

    /**
     * Flag indicating whether to leverage the result level cache for this query. When set to false, it disables
     * reading from the query cache for this query. When set to true, Druid uses druid.broker.cache.useResultLevelCache
     * to determine whether to read from the result-level query cache
     *
     * @param bool $useResultLevelCache
     *
     * @return $this
     */
    public function setUseResultLevelCache(bool $useResultLevelCache): self
    {
        $this->properties['useResultLevelCache'] = $useResultLevelCache;

        return $this;
    }

    /**
     * Flag indicating whether to save the results of the query to the result level cache. Primarily used for
     * debugging. When set to false, it disables saving the results of this query to the query cache. When set to true,
     * Druid uses druid.broker.cache.populateResultLevelCache to determine whether to save the results of this
     * query to the result-level query cache
     *
     * @param bool $populateResultLevelCache
     *
     * @return $this
     */
    public function setPopulateResultLevelCache(bool $populateResultLevelCache): self
    {
        $this->properties['populateResultLevelCache'] = $populateResultLevelCache;

        return $this;
    }

    /**
     * Return "by segment" results. Primarily used for debugging, setting it to true returns results associated with
     * the data segment they came from
     *
     * @param bool $bySegment
     *
     * @return $this
     */
    public function setBySegment(bool $bySegment): self
    {
        $this->properties['bySegment'] = $bySegment;

        return $this;
    }

    /**
     * Flag indicating whether to "finalize" aggregation results. Primarily used for debugging. For instance, the
     * hyperUnique aggregator will return the full HyperLogLog sketch instead of the estimated cardinality when this
     * flag is set to false
     *
     * @param bool $finalize
     *
     * @return $this
     */
    public function setFinalize(bool $finalize): self
    {
        $this->properties['finalize'] = $finalize;

        return $this;
    }

    /**
     * At the Broker process level, long interval queries (of any type) may be broken into shorter interval queries to
     * parallelize merging more than normal. Broken up queries will use a larger share of cluster resources, but, if
     * you use groupBy "v1", it may be able to complete faster as a result. Use ISO 8601 periods. For example, if this
     * property is set to P1M (one month), then a query covering a year would be broken into 12 smaller queries. The
     * broker uses its query processing executor service to initiate processing for query chunks, so make sure
     * "druid.processing.numThreads" is configured appropriately on the broker. groupBy queries do not support
     * chunkPeriod by default, although they do if using the legacy "v1" engine. This context is deprecated since it's
     * only useful for groupBy "v1", and will be removed in the future releases.
     *
     * @param string $chunkPeriod
     *
     * @return $this
     */
    public function setChunkPeriod(string $chunkPeriod): self
    {
        $this->properties['chunkPeriod'] = $chunkPeriod;

        return $this;
    }

    /**
     * Maximum number of bytes gathered from data processes such as historicals and realtime processes to execute a
     * query. This parameter can be used to further reduce maxScatterGatherBytes limit at query time. See Broker
     * configuration for more details.
     *
     * @param int $maxScatterGatherBytes
     *
     * @return $this
     */
    public function setMaxScatterGatherBytes(int $maxScatterGatherBytes): self
    {
        $this->properties['maxScatterGatherBytes'] = $maxScatterGatherBytes;

        return $this;
    }

    /**
     * Maximum number of bytes queued per query before exerting back pressure on the channel to the data server. Similar
     * to maxScatterGatherBytes, except unlike that configuration, this one will trigger back pressure rather than query
     * failure. Zero means disabled.
     *
     * @param int $maxQueuedBytes
     *
     * @return $this
     */
    public function setMaxQueuedBytes(int $maxQueuedBytes): self
    {
        $this->properties['maxQueuedBytes'] = $maxQueuedBytes;

        return $this;
    }

    /**
     * If true, DateTime is serialized as long in the result returned by Broker and the data transportation between
     * Broker and compute process
     *
     * @param bool $serializeDateTimeAsLong
     *
     * @return $this
     */
    public function setSerializeDateTimeAsLong(bool $serializeDateTimeAsLong): self
    {
        $this->properties['serializeDateTimeAsLong'] = $serializeDateTimeAsLong;

        return $this;
    }

    /**
     * If true, DateTime is serialized as long in the data transportation between Broker and compute process
     *
     * @param bool $serializeDateTimeAsLongInner
     *
     * @return $this
     */
    public function setSerializeDateTimeAsLongInner(bool $serializeDateTimeAsLongInner): self
    {
        $this->properties['serializeDateTimeAsLongInner'] = $serializeDateTimeAsLongInner;

        return $this;
    }

    /**
     * Enable parallel result merging on the Broker.
     * Note that druid.processing.merge.useParallelMergePool must be enabled for this setting to be
     * set to true. See Broker configuration for more details.
     *
     * @param bool $enableParallelMerge
     *
     * @return $this
     */
    public function setEnableParallelMerge(bool $enableParallelMerge): self
    {
        $this->properties['enableParallelMerge'] = $enableParallelMerge;

        return $this;
    }

    /**
     * Maximum number of parallel threads to use for parallel result merging on the Broker.
     * See Broker configuration for more details.
     *
     * @param int $parallelMergeParallelism
     *
     * @return $this
     */
    public function setParallelMergeParallelism(int $parallelMergeParallelism): self
    {
        $this->properties['parallelMergeParallelism'] = $parallelMergeParallelism;

        return $this;
    }

    /**
     * Number of rows to yield per ForkJoinPool merge task for parallel result merging on the Broker,
     * before forking off a new task to continue merging sequences.
     * See Broker configuration for more details.
     *
     * @param int $parallelMergeInitialYieldRows
     *
     * @return $this
     */
    public function setParallelMergeInitialYieldRows(int $parallelMergeInitialYieldRows): self
    {
        $this->properties['parallelMergeInitialYieldRows'] = $parallelMergeInitialYieldRows;

        return $this;
    }

    /**
     * Size of result batches to operate on in ForkJoinPool merge tasks for parallel result
     * merging on the Broker. See Broker configuration for more details.
     *
     * @param int $parallelMergeSmallBatchRows
     *
     * @return $this
     */
    public function setParallelMergeSmallBatchRows(int $parallelMergeSmallBatchRows): self
    {
        $this->properties['parallelMergeSmallBatchRows'] = $parallelMergeSmallBatchRows;

        return $this;
    }

    /**
     * If true, Druid will attempt to convert the query filter to Conjunctive Normal Form (CNF).
     * During query processing, columns can be pre-filtered by intersecting the bitmap indexes of
     * all values that match the eligible filters, often greatly reducing the raw number of rows
     * which need to be scanned. But this effect only happens for the top level filter,
     * or individual clauses of a top level 'and' filter. As such, filters in CNF potentially have
     * a higher chance to utilize a large amount of bitmap indexes on string columns during
     * pre-filtering. However, this setting should be used with great caution, as it can sometimes
     * have a negative effect on performance, and in some cases, the act of computing CNF of a
     * filter can be expensive. We recommend hand tuning your filters to produce an optimal form
     * if possible, or at least verifying through experimentation that using this parameter actually
     * improves your query performance with no ill-effects.
     *
     * @param bool $useFilterCNF
     *
     * @return $this
     */
    public function setUseFilterCNF(bool $useFilterCNF): self
    {
        $this->properties['useFilterCNF'] = $useFilterCNF;

        return $this;
    }

    /**
     * Enable secondary partition pruning on the Broker. The Broker will always prune
     * unnecessary segments from the input scan based on a filter on time intervals,
     * but if the data is further partitioned with hash or range partitioning,
     * this option will enable additional pruning based on a filter on secondary
     * partition dimensions.
     *
     * @param bool $secondaryPartitionPruning
     *
     * @return $this
     */
    public function setSecondaryPartitionPruning(bool $secondaryPartitionPruning): self
    {
        $this->properties['secondaryPartitionPruning'] = $secondaryPartitionPruning;

        return $this;
    }
}