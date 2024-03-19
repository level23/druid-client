<?php
declare(strict_types=1);

namespace Level23\Druid\Context;

/**
 * GroupBy queries can be executed using two different strategies. The default strategy for a cluster is determined
 * by the "druid.query.groupBy.defaultStrategy" runtime property on the Broker. This can be overridden using
 * "groupByStrategy" in the query context.
 *
 * Overrides the value of druid.query.groupBy.defaultStrategy for this query.
 */
class GroupByQueryContext extends QueryContext implements ContextInterface
{
    /**
     * @param array<string,string|int|bool> $properties
     */
    public function __construct(array $properties = [])
    {
        parent::__construct($properties);
    }

    /**
     * Overrides the value of druid.query.groupBy.singleThreaded for this query.
     *
     * @param bool $groupByIsSingleThreaded
     *
     * @return $this
     */
    public function setGroupByIsSingleThreaded(bool $groupByIsSingleThreaded): self
    {
        $this->properties['groupByIsSingleThreaded'] = $groupByIsSingleThreaded;

        return $this;
    }

    /**
     * Overrides the value of druid.query.groupBy.bufferGrouperInitialBuckets for this query.
     *
     * @param int $bufferGrouperInitialBuckets
     *
     * @return $this
     */
    public function setBufferGrouperInitialBuckets(int $bufferGrouperInitialBuckets): self
    {
        $this->properties['bufferGrouperInitialBuckets'] = $bufferGrouperInitialBuckets;

        return $this;
    }

    /**
     * Overrides the value of druid.query.groupBy.bufferGrouperMaxLoadFactor for this query.
     *
     * @param int $bufferGrouperMaxLoadFactor
     *
     * @return $this
     */
    public function setBufferGrouperMaxLoadFactor(int $bufferGrouperMaxLoadFactor): self
    {
        $this->properties['bufferGrouperMaxLoadFactor'] = $bufferGrouperMaxLoadFactor;

        return $this;
    }

    /**
     * Overrides the value of druid.query.groupBy.forceHashAggregation
     *
     * @param bool $forceHashAggregation
     *
     * @return $this
     */
    public function setForceHashAggregation(bool $forceHashAggregation): self
    {
        $this->properties['forceHashAggregation'] = $forceHashAggregation;

        return $this;
    }

    /**
     * Overrides the value of druid.query.groupBy.intermediateCombineDegree
     *
     * @param int $intermediateCombineDegree
     *
     * @return $this
     */
    public function setIntermediateCombineDegree(int $intermediateCombineDegree): self
    {
        $this->properties['intermediateCombineDegree'] = $intermediateCombineDegree;

        return $this;
    }

    /**
     * Overrides the value of druid.query.groupBy.numParallelCombineThreads
     *
     * @param int $numParallelCombineThreads
     *
     * @return $this
     */
    public function setNumParallelCombineThreads(int $numParallelCombineThreads): self
    {
        $this->properties['numParallelCombineThreads'] = $numParallelCombineThreads;

        return $this;
    }

    /**
     * Sort the results first by dimension values and then by timestamp.
     *
     * @param bool $sortByDimsFirst
     *
     * @return $this
     */
    public function setSortByDimsFirst(bool $sortByDimsFirst): self
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
     * @return $this
     */
    public function setForceLimitPushDown(bool $forceLimitPushDown): self
    {
        $this->properties['forceLimitPushDown'] = $forceLimitPushDown;

        return $this;
    }

    /**
     * Can be used to lower the value of druid.query.groupBy.maxMergingDictionarySize for this query.
     *
     * @param int $maxMergingDictionarySize
     *
     * @return $this
     */
    public function setMaxMergingDictionarySize(int $maxMergingDictionarySize): self
    {
        $this->properties['maxMergingDictionarySize'] = $maxMergingDictionarySize;

        return $this;
    }

    /**
     * Can be used to lower the value of druid.query.groupBy.maxOnDiskStorage for this query.
     *
     * @param int $maxOnDiskStorage
     *
     * @return $this
     */
    public function setMaxOnDiskStorage(int $maxOnDiskStorage): self
    {
        $this->properties['maxOnDiskStorage'] = $maxOnDiskStorage;

        return $this;
    }

    /**
     * If Broker pushes limit down to queryable nodes (historicals, peons) then limit results
     * during segment scan. This context value can be used to override
     * druid.query.groupBy.applyLimitPushDownToSegment.
     *
     * @param bool $applyLimitPushDownToSegment
     *
     * @return $this
     */
    public function setApplyLimitPushDownToSegment(bool $applyLimitPushDownToSegment): self
    {
        $this->properties['applyLimitPushDownToSegment'] = $applyLimitPushDownToSegment;

        return $this;
    }
}