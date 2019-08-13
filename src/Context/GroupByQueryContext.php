<?php
declare(strict_types=1);

namespace Level23\Druid\Context;

class GroupByQueryContext extends QueryContext implements ContextInterface
{
    /**
     * @var string
     *
     * GroupBy queries can be executed using two different strategies. The default strategy for a cluster is determined
     * by the "druid.query.groupBy.defaultStrategy" runtime property on the Broker. This can be overridden using
     * "groupByStrategy" in the query context. If neither the context field nor the property is set, the "v2" strategy
     * will be used.
     *
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
    public $groupByStrategy = 'v2';

    /**
     * Overrides the value of druid.query.groupBy.singleThreaded for this query.
     *
     * @var bool
     */
    public $groupByIsSingleThreaded;

    //<editor-fold desc="v2 options">

    /**
     * Overrides the value of druid.query.groupBy.bufferGrouperInitialBuckets for this query.
     *
     * @var int
     */
    public $bufferGrouperInitialBuckets;

    /**
     * Overrides the value of druid.query.groupBy.bufferGrouperMaxLoadFactor for this query.
     *
     * @var int
     */
    public $bufferGrouperMaxLoadFactor;

    /**
     * Overrides the value of druid.query.groupBy.forceHashAggregation
     *
     * @var bool
     */
    public $forceHashAggregation;

    /**
     * Overrides the value of druid.query.groupBy.intermediateCombineDegree
     *
     * @var int
     */
    public $intermediateCombineDegree;

    /**
     * Overrides the value of druid.query.groupBy.numParallelCombineThreads
     *
     * @var int
     */
    public $numParallelCombineThreads;

    /**
     * Sort the results first by dimension values and then by timestamp.
     *
     * @var bool
     */
    public $sortByDimsFirst;

    /**
     * When all fields in the orderBy are part of the grouping key, the Broker will push limit application down to the
     * Historical processes. When the sorting order uses fields that are not in the grouping key, applying this
     * optimization can result in approximate results with unknown accuracy, so this optimization is disabled by
     * default in that case. Enabling this context flag turns on limit push down for limit/orderBy's that contain
     * non-grouping key columns.
     *
     * @var bool
     */
    public $forceLimitPushDown;

    /**
     * Can be used to lower the value of druid.query.groupBy.maxMergingDictionarySize for this query.
     *
     * @var int
     */
    public $maxMergingDictionarySize;

    /**
     * Can be used to lower the value of druid.query.groupBy.maxOnDiskStorage for this query.
     *
     * @var int
     */
    public $maxOnDiskStorage;

    //</editor-fold>

    //<editor-fold desc="v1 options">

    /**
     * Can be used to lower the value of druid.query.groupBy.maxIntermediateRows for this query.
     *
     * @var int
     */
    public $maxIntermediateRows;

    /**
     * Can be used to lower the value of druid.query.groupBy.maxResults for this query.
     *
     * @var int
     */
    public $maxResults;

    /**
     * Set to true to store aggregations off-heap when merging results.
     *
     * @var bool
     */
    public $useOffheap;

    //</editor-fold>

    /**
     * Return the context as it can be used in the druid query.
     *
     * @return array
     */
    public function getContext(): array
    {
        $result = parent::getContext();

        $result['groupByStrategy'] = $this->groupByStrategy;

        if ($this->groupByStrategy == 'v1') {
            $properties = [
                'maxIntermediateRows',
                'maxResults',
                'useOffheap',
            ];
        }

        if ($this->groupByStrategy == 'v2') {
            $properties = [
                'bufferGrouperInitialBuckets',
                'bufferGrouperMaxLoadFactor',
                'forceHashAggregation',
                'intermediateCombineDegree',
                'numParallelCombineThreads',
                'sortByDimsFirst',
                'forceLimitPushDown',
                'maxMergingDictionarySize',
                'maxOnDiskStorage',
            ];
        }

        $properties[] = 'groupByIsSingleThreaded';

        foreach ($properties as $property) {
            if (!is_null($this->$property)) {
                $result[$property] = $this->$property;
            }
        }

        return $result;
    }
}