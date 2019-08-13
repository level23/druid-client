<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Level23\Druid\Aggregations\CountAggregator;
use Level23\Druid\Aggregations\DistinctCountAggregator;
use Level23\Druid\Aggregations\FirstAggregator;
use Level23\Druid\Aggregations\LastAggregator;
use Level23\Druid\Aggregations\MaxAggregator;
use Level23\Druid\Aggregations\MinAggregator;
use Level23\Druid\Aggregations\SumAggregator;
use Level23\Druid\Types\DataType;

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
     * @param string          $metric
     * @param string          $as
     * @param string|DataType $type
     *
     * @return $this
     */
    public function sum(string $metric, string $as = '', $type = 'long')
    {
        $this->aggregations[] = new SumAggregator($metric, $as, $type);

        return $this;
    }

    /**
     * Shorthand for summing long's
     *
     * @param string $metric
     * @param string $as
     *
     * @return $this
     */
    public function longSum(string $metric, string $as = '')
    {
        return $this->sum($metric, $as, 'long');
    }

    /**
     * Shorthand for summing doubles
     *
     * @param string $metric
     * @param string $as
     *
     * @return $this
     */
    public function doubleSum(string $metric, string $as = '')
    {
        return $this->sum($metric, $as, 'double');
    }

    /**
     * Shorthand for summing floats
     *
     * @param string $metric
     * @param string $as
     *
     * @return $this
     */
    public function floatSum(string $metric, string $as = '')
    {
        return $this->sum($metric, $as, 'float');
    }

    /**
     * Count the number of results and put it in a dimension with the given name.
     *
     * @param string $as
     *
     * @return $this
     */
    public function count(string $as)
    {
        $this->aggregations[] = new CountAggregator($as);

        return $this;
    }

    /**
     * Count the number of distinct values of a specific dimension.
     * NOTE: The DataSketches Theta Sketch extension is required to run this aggregation.
     *
     * @param string $dimension
     * @param string $as
     * @param int    $size Must be a power of 2. Internally, size refers to the maximum number of entries sketch object
     *                     will retain. Higher size means higher accuracy but more space to store sketches. Note that
     *                     after you index with a particular size, druid will persist sketch in segments and you will
     *                     use size greater or equal to that at query time. See the DataSketches site for details. In
     *                     general, We recommend just sticking to default size.
     *
     * @return $this
     */
    public function distinctCount(string $dimension, string $as = '', $size = 16384)
    {
        $this->aggregations[] = new DistinctCountAggregator($dimension, ($as ?: $dimension), $size);

        return $this;
    }

    /**
     * Get the minimum value for the given metric
     *
     * @param string $metric
     * @param string $as
     * @param string $type
     *
     * @return $this
     */
    public function min(string $metric, string $as = '', $type = 'long')
    {
        $this->aggregations[] = new MinAggregator($metric, $as, $type);

        return $this;
    }

    /**
     * Get the minimum value for the given metric using long as type
     *
     * @param string $metric
     * @param string $as
     *
     * @return $this
     */
    public function longMin(string $metric, string $as = '')
    {
        return $this->min($metric, $as, 'long');
    }

    /**
     * Get the minimum value for the given metric using double as type
     *
     * @param string $metric
     * @param string $as
     *
     * @return $this
     */
    public function doubleMin(string $metric, string $as = '')
    {
        return $this->min($metric, $as, 'double');
    }

    /**
     * Get the minimum value for the given metric using float as type
     *
     * @param string $metric
     * @param string $as
     *
     * @return $this
     */
    public function floatMin(string $metric, string $as = '')
    {
        return $this->min($metric, $as, 'float');
    }

    /**
     * Get the maximum value for the given metric
     *
     * @param string $metric
     * @param string $as
     * @param string $type
     *
     * @return $this
     */
    public function max(string $metric, string $as = '', $type = 'long')
    {
        $this->aggregations[] = new MaxAggregator($metric, $as, $type);

        return $this;
    }

    /**
     * Get the maximum value for the given metric using long as type
     *
     * @param string $metric
     * @param string $as
     *
     * @return $this
     */
    public function longMax(string $metric, string $as = '')
    {
        return $this->max($metric, $as, 'long');
    }

    /**
     * Get the maximum value for the given metric using float as type
     *
     * @param string $metric
     * @param string $as
     *
     * @return $this
     */
    public function floatMax(string $metric, string $as = '')
    {
        return $this->max($metric, $as, 'float');
    }

    /**
     * Get the maximum value for the given metric using double as type
     *
     * @param string $metric
     * @param string $as
     *
     * @return $this
     */
    public function doubleMax(string $metric, string $as = '')
    {
        return $this->max($metric, $as, 'double');
    }

    /**
     * Get the first metric found based on the applied group-by filters.
     * So if you group by the dimension "countries", you can get the first "metric" per country.
     *
     * NOTE: This is different then the ELOQUENT first() method!
     *
     * @param string $metric
     * @param string $as
     * @param string $type
     *
     * @return $this
     */
    public function first(string $metric, string $as = '', $type = 'long')
    {
        $this->aggregations[] = new FirstAggregator($metric, $as, $type);

        return $this;
    }

    /**
     * Get the last metric found
     *
     * @param string $metric
     * @param string $as
     * @param string $type
     *
     * @return $this
     */
    public function last(string $metric, string $as = '', $type = 'long')
    {
        $this->aggregations[] = new LastAggregator($metric, $as, $type);

        return $this;
    }
}