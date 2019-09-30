<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Closure;
use Level23\Druid\Filters\FilterBuilder;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Aggregations\MaxAggregator;
use Level23\Druid\Aggregations\MinAggregator;
use Level23\Druid\Aggregations\SumAggregator;
use Level23\Druid\Aggregations\LastAggregator;
use Level23\Druid\Dimensions\DimensionBuilder;
use Level23\Druid\Aggregations\CountAggregator;
use Level23\Druid\Aggregations\FirstAggregator;
use Level23\Druid\Aggregations\FilteredAggregator;
use Level23\Druid\Collections\DimensionCollection;
use Level23\Druid\Aggregations\AggregatorInterface;
use Level23\Druid\Aggregations\JavascriptAggregator;
use Level23\Druid\Aggregations\HyperUniqueAggregator;
use Level23\Druid\Aggregations\CardinalityAggregator;
use Level23\Druid\Aggregations\DistinctCountAggregator;

trait HasAggregations
{
    /**
     * @var array|\Level23\Druid\Aggregations\AggregatorInterface[]
     */
    protected $aggregations = [];

    /**
     * @return array|\Level23\Druid\Aggregations\AggregatorInterface[]
     */
    public function getAggregations(): array
    {
        return $this->aggregations;
    }

    /**
     * Sum the given metric
     *
     * @param string        $metric
     * @param string        $as
     * @param string        $type
     * @param \Closure|null $filterBuilder   A closure which receives a FilterBuilder. When given, we will only apply
     *                                       the "sum" function to the records which match with the given filter.
     *
     * @return $this
     */
    public function sum(string $metric, string $as = '', string $type = 'long', Closure $filterBuilder = null)
    {
        $this->aggregations[] = $this->buildFilteredAggregation(
            new SumAggregator($metric, $as, $type),
            $filterBuilder
        );

        return $this;
    }

    /**
     * Shorthand for summing long's
     *
     * @param string        $metric
     * @param string        $as
     * @param \Closure|null $filterBuilder   A closure which receives a FilterBuilder. When given, we will only apply
     *                                       the "sum" function to the records which match with the given filter.
     *
     * @return $this
     */
    public function longSum(string $metric, string $as = '', Closure $filterBuilder = null)
    {
        return $this->sum($metric, $as, 'long', $filterBuilder);
    }

    /**
     * Shorthand for summing doubles
     *
     * @param string        $metric
     * @param string        $as
     * @param \Closure|null $filterBuilder   A closure which receives a FilterBuilder. When given, we will only apply
     *                                       the "sum" function to the records which match with the given filter.
     *
     * @return $this
     */
    public function doubleSum(string $metric, string $as = '', Closure $filterBuilder = null)
    {
        return $this->sum($metric, $as, 'double', $filterBuilder);
    }

    /**
     * Shorthand for summing floats
     *
     * @param string        $metric
     * @param string        $as
     * @param \Closure|null $filterBuilder   A closure which receives a FilterBuilder. When given, we will only apply
     *                                       the "sum" function to the records which match with the given filter.
     *
     * @return $this
     */
    public function floatSum(string $metric, string $as = '', Closure $filterBuilder = null)
    {
        return $this->sum($metric, $as, 'float', $filterBuilder);
    }

    /**
     * Uses HyperLogLog to compute the estimated cardinality of a dimension that has been aggregated as a "hyperUnique"
     * metric at indexing time.
     *
     * @see http://algo.inria.fr/flajolet/Publications/FlFuGaMe07.pdf
     *
     * @param string $metric
     * @param string $as
     * @param bool   $round              Only affects query-time behavior, and is ignored at ingestion-time. The
     *                                   HyperLogLog algorithm generates decimal estimates with some error. "round" can
     *                                   be set to true to round off estimated values to whole numbers. Note that even
     *                                   with rounding, the cardinality is still an estimate.
     * @param bool   $isInputHyperUnique Only affects ingestion-time behavior, and is ignored at query-time. Set to
     *                                   true to index pre-computed HLL (Base64 encoded output from druid-hll is
     *                                   expected).
     *
     * @return $this
     */
    public function hyperUnique(string $metric, string $as, bool $round = false, bool $isInputHyperUnique = false)
    {
        $this->aggregations[] = new HyperUniqueAggregator(
            $as, $metric, $isInputHyperUnique, $round
        );

        return $this;
    }

    /**
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
     * @param string   $as               The output name which is used for the result.
     * @param \Closure $dimensionBuilder A closure which will receive a DimensionBuilder. You should build the
     *                                   dimensions which are used to calculate the cardinality over.
     * @param bool     $byRow            For more details see method description.
     * @param bool     $round            Only affects query-time behavior, and is ignored at ingestion-time. The
     *                                   HyperLogLog algorithm generates decimal estimates with some error. "round" can
     *                                   be set to true to round off estimated values to whole numbers. Note that even
     *                                   with rounding, the cardinality is still an estimate.
     *
     * @return $this
     */
    public function cardinality(string $as, Closure $dimensionBuilder, bool $byRow = false, bool $round = false)
    {
        $builder = new DimensionBuilder();
        call_user_func($dimensionBuilder, $builder);
        $dimensions = $builder->getDimensions();

        $this->aggregations[] = new CardinalityAggregator(
            $as,
            new DimensionCollection(...$dimensions),
            $byRow,
            $round
        );

        return $this;
    }

    /**
     * When a closure is given, we will call the given function which is responsible for building a filter.
     * We will then only apply the given aggregator for the records where the filter matches.
     *
     * @param \Level23\Druid\Aggregations\AggregatorInterface $aggregator
     * @param \Closure|null                                   $filterBuilder
     *
     * @return \Level23\Druid\Aggregations\AggregatorInterface
     */
    protected function buildFilteredAggregation(
        AggregatorInterface $aggregator,
        Closure $filterBuilder = null
    ): AggregatorInterface {
        if (!$filterBuilder) {
            return $aggregator;
        }

        $builder = new FilterBuilder();
        call_user_func($filterBuilder, $builder);
        $filter = $builder->getFilter();

        if ($filter instanceof FilterInterface) {
            return new FilteredAggregator($filter, $aggregator);
        }

        return $aggregator;
    }

    /**
     * Count the number of results and put it in a dimension with the given name.
     *
     * @param string        $as
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only count the
     *                                     records which match with the given filter.
     *
     * @return $this
     */
    public function count(string $as, Closure $filterBuilder = null)
    {
        $this->aggregations[] = $this->buildFilteredAggregation(
            new CountAggregator($as),
            $filterBuilder
        );

        return $this;
    }

    /**
     * Count the number of distinct values of a specific dimension.
     * NOTE: The DataSketches Theta Sketch extension is required to run this aggregation.
     *
     * @param string        $dimension
     * @param string        $as
     * @param int           $size          Must be a power of 2. Internally, size refers to the maximum number of
     *                                     entries sketch object will retain. Higher size means higher accuracy but
     *                                     more space to store sketches. Note that after you index with a particular
     *                                     size, druid will persist sketch in segments and you will use size greater or
     *                                     equal to that at query time. See the DataSketches site for details. In
     *                                     general, We recommend just sticking to default size.
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only count the
     *                                     records which match with the given filter.
     *
     * @return $this
     */
    public function distinctCount(string $dimension, string $as = '', $size = 16384, Closure $filterBuilder = null)
    {
        $this->aggregations[] = $this->buildFilteredAggregation(
            new DistinctCountAggregator($dimension, ($as ?: $dimension), $size),
            $filterBuilder
        );

        return $this;
    }

    /**
     * Get the minimum value for the given metric
     *
     * @param string        $metric
     * @param string        $as
     * @param string        $type
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only apply the
     *                                     min function to the records which match with the given filter.
     *
     * @return $this
     */
    public function min(string $metric, string $as = '', $type = 'long', Closure $filterBuilder = null)
    {
        $this->aggregations[] = $this->buildFilteredAggregation(
            new MinAggregator($metric, $as, $type),
            $filterBuilder
        );

        return $this;
    }

    /**
     * Get the minimum value for the given metric using long as type
     *
     * @param string        $metric
     * @param string        $as
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only apply the
     *                                     min function to the records which match with the given filter.
     *
     * @return $this
     */
    public function longMin(string $metric, string $as = '', Closure $filterBuilder = null)
    {
        return $this->min($metric, $as, 'long', $filterBuilder);
    }

    /**
     * Get the minimum value for the given metric using double as type
     *
     * @param string        $metric
     * @param string        $as
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only apply the
     *                                     min function to the records which match with the given filter.
     *
     * @return $this
     */
    public function doubleMin(string $metric, string $as = '', Closure $filterBuilder = null)
    {
        return $this->min($metric, $as, 'double', $filterBuilder);
    }

    /**
     * Get the minimum value for the given metric using float as type
     *
     * @param string        $metric
     * @param string        $as
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only apply the
     *                                     min function to the records which match with the given filter.
     *
     * @return $this
     */
    public function floatMin(string $metric, string $as = '', Closure $filterBuilder = null)
    {
        return $this->min($metric, $as, 'float', $filterBuilder);
    }

    /**
     * Get the maximum value for the given metric
     *
     * @param string        $metric
     * @param string        $as
     * @param string        $type
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only apply the
     *                                     max function to the records which match with the given filter.
     *
     * @return $this
     */
    public function max(string $metric, string $as = '', $type = 'long', Closure $filterBuilder = null)
    {
        $this->aggregations[] = $this->buildFilteredAggregation(
            new MaxAggregator($metric, $as, $type),
            $filterBuilder
        );

        return $this;
    }

    /**
     * Get the maximum value for the given metric using long as type
     *
     * @param string        $metric
     * @param string        $as
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only apply the
     *                                     max function to the records which match with the given filter.
     *
     * @return $this
     */
    public function longMax(string $metric, string $as = '', Closure $filterBuilder = null)
    {
        return $this->max($metric, $as, 'long', $filterBuilder);
    }

    /**
     * Get the maximum value for the given metric using float as type
     *
     * @param string        $metric
     * @param string        $as
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only apply the
     *                                     max function to the records which match with the given filter.
     *
     * @return $this
     */
    public function floatMax(string $metric, string $as = '', Closure $filterBuilder = null)
    {
        return $this->max($metric, $as, 'float', $filterBuilder);
    }

    /**
     * Get the maximum value for the given metric using double as type
     *
     * @param string        $metric
     * @param string        $as
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only apply the
     *                                     max function to the records which match with the given filter.
     *
     * @return $this
     */
    public function doubleMax(string $metric, string $as = '', Closure $filterBuilder = null)
    {
        return $this->max($metric, $as, 'double', $filterBuilder);
    }

    /**
     * Get the first metric found based on the applied group-by filters.
     * So if you group by the dimension "countries", you can get the first "metric" per country.
     *
     * NOTE: This is different then the ELOQUENT first() method!
     *
     * @param string        $metric
     * @param string        $as
     * @param string        $type
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only apply the
     *                                     "first" function to the records which match with the given filter.
     *
     * @return $this
     */
    public function first(string $metric, string $as = '', $type = 'long', Closure $filterBuilder = null)
    {
        $this->aggregations[] = $this->buildFilteredAggregation(
            new FirstAggregator($metric, $as, $type),
            $filterBuilder
        );

        return $this;
    }

    /**
     * Get the first metric found based on the applied group-by filters.
     * So if you group by the dimension "countries", you can get the first "metric" per country.
     *
     * NOTE: This is different then the ELOQUENT first() method!
     *
     * @param string        $metric
     * @param string        $as
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only apply the
     *                                     "first" function to the records which match with the given filter.
     *
     * @return $this
     */
    public function longFirst(string $metric, string $as = '', Closure $filterBuilder = null)
    {
        return $this->first($metric, $as, 'long', $filterBuilder);
    }

    /**
     * Get the first metric found based on the applied group-by filters.
     * So if you group by the dimension "countries", you can get the first "metric" per country.
     *
     * NOTE: This is different then the ELOQUENT first() method!
     *
     * @param string        $metric
     * @param string        $as
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only apply the
     *                                     "first" function to the records which match with the given filter.
     *
     * @return $this
     */
    public function floatFirst(string $metric, string $as = '', Closure $filterBuilder = null)
    {
        return $this->first($metric, $as, 'float', $filterBuilder);
    }

    /**
     * Get the first metric found based on the applied group-by filters.
     * So if you group by the dimension "countries", you can get the first "metric" per country.
     *
     * NOTE: This is different then the ELOQUENT first() method!
     *
     * @param string        $metric
     * @param string        $as
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only apply the
     *                                     "first" function to the records which match with the given filter.
     *
     * @return $this
     */
    public function doubleFirst(string $metric, string $as = '', Closure $filterBuilder = null)
    {
        return $this->first($metric, $as, 'double', $filterBuilder);
    }

    /**
     * Get the first metric found based on the applied group-by filters.
     * So if you group by the dimension "countries", you can get the first "metric" per country.
     *
     * NOTE: This is different then the ELOQUENT first() method!
     *
     * @param string        $metric
     * @param string        $as
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only apply the
     *                                     "first" function to the records which match with the given filter.
     *
     * @return $this
     */
    public function stringFirst(string $metric, string $as = '', Closure $filterBuilder = null)
    {
        return $this->first($metric, $as, 'string', $filterBuilder);
    }

    /**
     * Get the last metric found
     *
     * @param string        $metric
     * @param string        $as
     * @param string        $type
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only apply the
     *                                     "last" function to the records which match with the given filter.
     *
     * @return $this
     */
    public function last(string $metric, string $as = '', $type = 'long', Closure $filterBuilder = null)
    {
        $this->aggregations[] = $this->buildFilteredAggregation(
            new LastAggregator($metric, $as, $type),
            $filterBuilder
        );

        return $this;
    }

    /**
     * Get the last metric found based on the applied group-by filters.
     * So if you group by the dimension "countries", you can get the last "metric" per country.
     *
     * NOTE: This is different then the ELOQUENT last() method!
     *
     * @param string        $metric
     * @param string        $as
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only apply the
     *                                     "last" function to the records which match with the given filter.
     *
     * @return $this
     */
    public function longLast(string $metric, string $as = '', Closure $filterBuilder = null)
    {
        return $this->last($metric, $as, 'long', $filterBuilder);
    }

    /**
     * Get the last metric found based on the applied group-by filters.
     * So if you group by the dimension "countries", you can get the last "metric" per country.
     *
     * NOTE: This is different then the ELOQUENT last() method!
     *
     * @param string        $metric
     * @param string        $as
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only apply the
     *                                     "last" function to the records which match with the given filter.
     *
     * @return $this
     */
    public function floatLast(string $metric, string $as = '', Closure $filterBuilder = null)
    {
        return $this->last($metric, $as, 'float', $filterBuilder);
    }

    /**
     * Get the last metric found based on the applied group-by filters.
     * So if you group by the dimension "countries", you can get the last "metric" per country.
     *
     * NOTE: This is different then the ELOQUENT last() method!
     *
     * @param string        $metric
     * @param string        $as
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only apply the
     *                                     "last" function to the records which match with the given filter.
     *
     * @return $this
     */
    public function doubleLast(string $metric, string $as = '', Closure $filterBuilder = null)
    {
        return $this->last($metric, $as, 'double', $filterBuilder);
    }

    /**
     * Get the last metric found based on the applied group-by filters.
     * So if you group by the dimension "countries", you can get the last "metric" per country.
     *
     * NOTE: This is different then the ELOQUENT last() method!
     *
     * @param string        $metric
     * @param string        $as
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only apply the
     *                                     "last" function to the records which match with the given filter.
     *
     * @return $this
     */
    public function stringLast(string $metric, string $as = '', Closure $filterBuilder = null)
    {
        return $this->last($metric, $as, 'string', $filterBuilder);
    }

    /**
     * Computes an arbitrary JavaScript function over a set of columns (both metrics and dimensions are allowed). Your
     * JavaScript functions are expected to return floating-point values.
     *
     * Note: JavaScript-based functionality is disabled by default. Please refer to the Druid JavaScript programming
     * guide for guidelines about using Druid's JavaScript functionality, including instructions on how to enable it.
     *
     * @param string        $as            The output name as the result will be available
     * @param array         $fieldNames    The columns which will be given to the fnAggregate function. Both metrics
     *                                     and dimensions are allowed.
     * @param string        $fnAggregate   A javascript function which does the aggregation. This function will receive
     *                                     the "current" value as first parameter. The other parameters will be the
     *                                     values of the columns as given in the $fieldNames parameter.
     * @param string        $fnCombine     A function which can combine two aggregation results.
     * @param string        $fnReset       A function which will reset a value.
     * @param \Closure|null $filterBuilder A closure which receives a FilterBuilder. When given, we will only apply the
     *                                     javascript function to the records which match with the given filter.
     *
     * @return $this
     */
    public function javascript(
        string $as,
        array $fieldNames,
        string $fnAggregate,
        string $fnCombine,
        string $fnReset,
        Closure $filterBuilder = null
    ) {
        $this->aggregations[] = $this->buildFilteredAggregation(
            new JavascriptAggregator($fieldNames, $as, $fnAggregate, $fnCombine, $fnReset),
            $filterBuilder
        );

        return $this;
    }
}