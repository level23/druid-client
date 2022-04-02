<?php
declare(strict_types=1);

namespace Level23\Druid\Aggregations;

use Level23\Druid\Collections\DimensionCollection;

class CardinalityAggregator implements AggregatorInterface
{
    protected string $outputName;

    protected bool $byRow;

    protected bool $round;

    protected DimensionCollection $dimensions;

    /**
     * CardinalityAggregator constructor.
     *
     * Computes the cardinality of a set of Apache Druid (incubating) dimensions, using HyperLogLog to estimate the
     * cardinality. Please note that this aggregator will be much slower than indexing a column with the hyperUnique
     * aggregator. This aggregator also runs over a dimension column, which means the string dimension cannot be
     * removed from the dataset to improve rollup. In general, we strongly recommend using the hyperUnique aggregator
     * instead of the cardinality aggregator if you do not care about the individual values of a dimension.
     *
     * The HyperLogLog algorithm generates decimal estimates with some error. "round" can be set to true to round off
     * estimated values to whole numbers. Note that even with rounding, the cardinality is still an estimate. The
     * "round" field only affects query-time behavior, and is ignored at ingestion-time.
     *
     * When setting byRow to false (the default) it computes the cardinality of the set composed of the union of all
     * dimension values for all the given dimensions. For a single dimension, this is equivalent to:
     * ```
     * SELECT COUNT(DISTINCT(dimension)) FROM <datasource>
     * ```
     *
     * For multiple dimensions, this is equivalent to something akin to
     * ```
     * SELECT COUNT(DISTINCT(value)) FROM (
     * SELECT dim_1 as value FROM <datasource>
     * UNION
     * SELECT dim_2 as value FROM <datasource>
     * UNION
     * SELECT dim_3 as value FROM <datasource>
     * )
     * ```
     *
     * When setting byRow to true it computes the cardinality by row, i.e. the cardinality of distinct dimension
     * combinations. This is equivalent to something akin to
     *
     * ```
     * SELECT COUNT(*) FROM ( SELECT DIM1, DIM2, DIM3 FROM <datasource> GROUP BY DIM1, DIM2, DIM3 )
     * ```
     *
     * @see https://druid.apache.org/docs/latest/querying/hll-old.html
     *
     * @param string                                         $outputName
     * @param \Level23\Druid\Collections\DimensionCollection $dimensions
     * @param bool                                           $byRow
     * @param bool                                           $round
     */
    public function __construct(
        string $outputName,
        DimensionCollection $dimensions,
        bool $byRow = false,
        bool $round = false
    ) {
        $this->outputName = $outputName;
        $this->byRow      = $byRow;
        $this->round      = $round;
        $this->dimensions = $dimensions;
    }

    /**
     * Return the aggregator as it can be used in a druid query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'   => 'cardinality',
            'name'   => $this->outputName,
            'fields' => $this->dimensions->toArray(),
            'byRow'  => $this->byRow,
            'round'  => $this->round,
        ];
    }
}