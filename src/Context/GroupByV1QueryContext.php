<?php
declare(strict_types=1);

namespace Level23\Druid\Context;

/**
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
 * merging is multithreaded by default, but can optionally be single-threaded. The Broker merges the final result
 * set using Druid's indexing mechanism again. The broker merging is always single-threaded. Because the Broker
 * merges results using the indexing mechanism, it must materialize the full result set before returning any
 * results. On both the data processes and the Broker, the merging index is fully on-heap by default, but it can
 * optionally store aggregated values off-heap.
 *
 * Overrides the value of druid.query.groupBy.defaultStrategy for this query.
 */
class GroupByV1QueryContext extends QueryContext implements ContextInterface
{
    /**
     * @param array<string,string|int|bool> $properties
     */
    public function __construct(array $properties)
    {
        parent::__construct($properties);

        $this->properties['groupByStrategy'] = 'v1';
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
     * Can be used to lower the value of druid.query.groupBy.maxIntermediateRows for this query.
     *
     * @param int $maxIntermediateRows
     *
     * @return $this
     */
    public function setMaxIntermediateRows(int $maxIntermediateRows): self
    {
        $this->properties['maxIntermediateRows'] = $maxIntermediateRows;

        return $this;
    }

    /**
     * Can be used to lower the value of druid.query.groupBy.maxResults for this query.
     *
     * @param int $maxResults
     *
     * @return $this
     */
    public function setMaxResults(int $maxResults): self
    {
        $this->properties['maxResults'] = $maxResults;

        return $this;
    }

    /**
     * Set to true to store aggregations off-heap when merging results.
     *
     * @param bool $useOffheap
     *
     * @return $this
     */
    public function setUseOffheap(bool $useOffheap): self
    {
        $this->properties['useOffheap'] = $useOffheap;

        return $this;
    }
}