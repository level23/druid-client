<?php
declare(strict_types=1);

namespace Level23\Druid\Context;

/**
 * GroupBy queries can be executed using two different strategies. The default strategy for a cluster is determined
 * by the "druid.query.groupBy.defaultStrategy" runtime property on the Broker. This can be overridden using
 * "groupByStrategy" in the query context. If neither the context field nor the property is set, the "v2" strategy
 * will be used.
 *
 * "v2", the default, is designed to offer better performance and memory management. This strategy generates
 * per-segment results using a fully off-heap map. Data processes merge the per-segment results using a fully
 * off-heap concurrent facts map combined with an on-heap string dictionary. This may optionally involve spilling
 * to disk. Data processes return sorted results to the Broker, which merges result streams using an N-way merge.
 * The broker materializes the results if necessary (e.g. if the query sorts on columns other than its dimensions).
 * Otherwise, it streams results back as they are merged.
 *
 * "v1", a legacy engine, generates per-segment results on data processes (Historical, realtime, MiddleManager)
 * using a map which is partially on-heap (dimension keys and the map itself) and partially off-heap (the
 * aggregated values). Data processes then merge the per-segment results using Druid's indexing mechanism. This
 * merging is multi-threaded by default, but can optionally be single-threaded. The Broker merges the final result
 * set using Druid's indexing mechanism again. The broker merging is always single-threaded. Because the Broker
 * merges results using the indexing mechanism, it must materialize the full result set before returning any
 * results. On both the data processes and the Broker, the merging index is fully on-heap by default, but it can
 * optionally store aggregated values off-heap.
 *
 * Overrides the value of druid.query.groupBy.defaultStrategy for this query.
 */
class GroupByV2QueryContext extends QueryContext implements ContextInterface
{
    public function __construct(array $properties)
    {
        parent::__construct($properties);

        $this->properties['groupByStrategy'] = 'v2';
    }

    /**
     * Overrides the value of druid.query.groupBy.singleThreaded for this query.
     *
     * @param bool $groupByIsSingleThreaded
     *
     * @return $this;
     */
    public function setGroupByIsSingleThreaded(bool $groupByIsSingleThreaded)
    {
        $this->properties['groupByIsSingleThreaded'] = $groupByIsSingleThreaded;

        return $this;
    }

    /**
     * Overrides the value of druid.query.groupBy.bufferGrouperInitialBuckets for this query.
     *
     * @param int $bufferGrouperInitialBuckets
     *
     * @return $this;
     */
    public function setBufferGrouperInitialBuckets(int $bufferGrouperInitialBuckets)
    {
        $this->properties['bufferGrouperInitialBuckets'] = $bufferGrouperInitialBuckets;

        return $this;
    }

    /**
     * Overrides the value of druid.query.groupBy.bufferGrouperMaxLoadFactor for this query.
     *
     * @param int $bufferGrouperMaxLoadFactor
     *
     * @return $this;
     */
    public function setBufferGrouperMaxLoadFactor(int $bufferGrouperMaxLoadFactor)
    {
        $this->properties['bufferGrouperMaxLoadFactor'] = $bufferGrouperMaxLoadFactor;

        return $this;
    }

    /**
     * Overrides the value of druid.query.groupBy.forceHashAggregation
     *
     * @param bool $forceHashAggregation
     *
     * @return $this;
     */
    public function setForceHashAggregation(bool $forceHashAggregation)
    {
        $this->properties['forceHashAggregation'] = $forceHashAggregation;

        return $this;
    }

    /**
     * Overrides the value of druid.query.groupBy.intermediateCombineDegree
     *
     * @param int $intermediateCombineDegree
     *
     * @return $this;
     */
    public function setIntermediateCombineDegree(int $intermediateCombineDegree)
    {
        $this->properties['intermediateCombineDegree'] = $intermediateCombineDegree;

        return $this;
    }

    /**
     * Overrides the value of druid.query.groupBy.numParallelCombineThreads
     *
     * @param int $numParallelCombineThreads
     *
     * @return $this;
     */
    public function setNumParallelCombineThreads(int $numParallelCombineThreads)
    {
        $this->properties['numParallelCombineThreads'] = $numParallelCombineThreads;

        return $this;
    }

    /**
     * Sort the results first by dimension values and then by timestamp.
     *
     * @param bool $sortByDimsFirst
     *
     * @return $this;
     */
    public function setSortByDimsFirst(bool $sortByDimsFirst)
    {
        $this->properties['sortByDimsFirst'] = $sortByDimsFirst;

        return $this;
    }

    /**
     * When all fields in the orderBy are part of the grouping key, the Broker will push limit application down to the
     * Historical processes. When the sorting order uses fields that are not in the grouping key, applying this
     * optimization can result in approximate results with unknown accuracy, so this optimization is disabled by
     * default in that case. Enabling this context flag turns on limit push down for limit/orderBy's that contain
     * non-grouping key columns.
     *
     * @param bool $forceLimitPushDown
     *
     * @return $this;
     */
    public function setForceLimitPushDown(bool $forceLimitPushDown)
    {
        $this->properties['forceLimitPushDown'] = $forceLimitPushDown;

        return $this;
    }

    /**
     * Can be used to lower the value of druid.query.groupBy.maxMergingDictionarySize for this query.
     *
     * @param int $maxMergingDictionarySize
     *
     * @return $this;
     */
    public function setMaxMergingDictionarySize(int $maxMergingDictionarySize)
    {
        $this->properties['maxMergingDictionarySize'] = $maxMergingDictionarySize;

        return $this;
    }

    /**
     * Can be used to lower the value of druid.query.groupBy.maxOnDiskStorage for this query.
     *
     * @param int $maxOnDiskStorage
     *
     * @return $this;
     */
    public function setMaxOnDiskStorage(int $maxOnDiskStorage)
    {
        $this->properties['maxOnDiskStorage'] = $maxOnDiskStorage;

        return $this;
    }
}