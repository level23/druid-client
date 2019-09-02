<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Level23\Druid\Types\ArithmeticFunction;
use Level23\Druid\PostAggregations\MinPostAggregator;
use Level23\Druid\PostAggregations\MaxPostAggregator;
use Level23\Druid\PostAggregations\LeastPostAggregator;
use Level23\Druid\PostAggregations\ConstantPostAggregator;
use Level23\Druid\PostAggregations\GreatestPostAggregator;
use Level23\Druid\PostAggregations\ArithmeticPostAggregator;
use Level23\Druid\PostAggregations\JavaScriptPostAggregator;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;
use Level23\Druid\PostAggregations\HyperUniqueCardinalityPostAggregator;

trait HasPostAggregations
{
    /**
     * @var array|\Level23\Druid\PostAggregations\PostAggregatorInterface[]
     */
    protected $postAggregations = [];

    /**
     * The arithmetic post-aggregator applies the provided function to the given fields from left to right. The fields
     * can be aggregators or other post aggregators.
     *
     * Notes:
     * -  / division always returns 0 if dividing by 0, regardless of the numerator.
     * - quotient division behaves like regular floating point division
     *
     * @param array|string[]            $fields                List with field names which are used for this function
     * @param string|ArithmeticFunction $function              Supported functions are +, -, *, /, and quotient.
     * @param string                    $as                    The output name
     * @param bool                      $floatingPointOrdering By default floating point ordering is used. When set to
     *                                                         false we will use numericFirst ordering. It returns
     *                                                         finite values first, followed by NaN, and infinite
     *                                                         values last.
     *
     * @return $this
     */
    public function arithmetic(array $fields, $function, string $as, bool $floatingPointOrdering = true)
    {
        $this->postAggregations[] = new ArithmeticPostAggregator(
            $as,
            $function,
            $fields,
            $floatingPointOrdering
        );

        return $this;
    }

    /**
     * Field accessor post-aggregators
     *
     * These post-aggregators return the value produced by the specified aggregator.
     *
     * $aggregatorOutputName refers to the output name of the aggregator given in the aggregations portion of the
     * query. For complex aggregators, like "cardinality" and "hyperUnique", the type of the post-aggregator determines
     * what the post-aggregator will return.
     * Set $finalizing to `false`  to return the raw aggregation object, or use `true`
     * to return a finalized value, such as an estimated cardinality.
     *
     * @param string $aggregatorOutputName
     * @param string $as
     * @param bool   $finalizing
     *
     * @return $this
     */
    public function fieldAccess(string $aggregatorOutputName, string $as, bool $finalizing = false)
    {
        $this->postAggregations[] = new FieldAccessPostAggregator($aggregatorOutputName, $as, $finalizing);

        return $this;
    }

    /**
     * The constant post-aggregator always returns the specified value.
     *
     * @param int|float $numericValue
     * @param string    $as
     *
     * @return $this
     */
    public function constant($numericValue, string $as)
    {
        $this->postAggregations[] = new ConstantPostAggregator($as, $numericValue);

        return $this;
    }

    /**
     * Return the lowest value of all rows for one specific column.
     *
     * @param array|string[] $fields
     * @param string         $as   The output name
     * @param string         $type Either "long" or "double"
     *
     * @return $this
     */
    public function min(array $fields, string $as, string $type = 'long')
    {
        $this->postAggregations[] = new MinPostAggregator($as, $fields, $type);

        return $this;
    }

    /**
     * Return the lowest value of all rows for one specific column.
     *
     * @param array|string[] $fields
     * @param string         $as The output name
     *
     * @return $this
     */
    public function longMin(array $fields, string $as)
    {
        return $this->min($fields, $as, 'long');
    }

    /**
     * Return the lowest value of all rows for one specific column.
     *
     * @param array|string[] $fields
     * @param string         $as The output name
     *
     * @return $this
     */
    public function doubleMin(array $fields, string $as)
    {
        return $this->min($fields, $as, 'double');
    }

    /**
     * Return the highest value of all rows for one specific column.
     *
     * @param array|string[] $fields
     * @param string         $as   The output name
     * @param string         $type Either "long" or "double"
     *
     * @return $this
     */
    public function max(array $fields, string $as, string $type = 'long')
    {
        $this->postAggregations[] = new MaxPostAggregator($as, $fields, $type);

        return $this;
    }

    /**
     * Return the highest value of all rows for one specific column.
     *
     * @param array|string[] $fields
     * @param string         $as The output name
     *
     * @return $this
     */
    public function longMax(array $fields, string $as)
    {
        return $this->max($fields, $as, 'long');
    }

    /**
     * Return the highest value of all rows for one specific column.
     *
     * @param array|string[] $fields
     * @param string         $as The output name
     *
     * @return $this
     */
    public function doubleMax(array $fields, string $as)
    {
        return $this->max($fields, $as, 'double');
    }

    /**
     * Return the highest value of multiple columns in one row.
     *
     * @param array|string[] $fields
     * @param string         $as   The output name
     * @param string         $type Either "long" or "double"
     *
     * @return $this
     */
    public function greatest(array $fields, string $as, string $type = 'long')
    {
        $this->postAggregations[] = new GreatestPostAggregator($as, $fields, $type);

        return $this;
    }

    /**
     * Return the highest value of multiple columns in one row.
     *
     * @param array|string[] $fields
     * @param string         $as The output name
     *
     * @return $this
     */
    public function longGreatest(array $fields, string $as)
    {
        return $this->greatest($fields, $as, 'long');
    }

    /**
     * Return the highest value of multiple columns in one row.
     *
     * @param array|string[] $fields
     * @param string         $as The output name
     *
     * @return $this
     */
    public function doubleGreatest(array $fields, string $as)
    {
        return $this->greatest($fields, $as, 'double');
    }

    /**
     * Return the lowest value of multiple columns in one row.
     *
     * @param array|string[] $fields
     * @param string         $as The output name
     * @param string         $type Either "long" or "double"
     *
     * @return $this
     */
    public function least(array $fields, string $as, string $type = 'long')
    {
        $this->postAggregations[] = new LeastPostAggregator($as, $fields, $type);

        return $this;
    }

    /**
     * Return the lowest value of multiple columns in one row.
     *
     * @param array|string[] $fields
     * @param string         $as The output name
     *
     * @return $this
     */
    public function longLeast(array $fields, string $as)
    {
        return $this->least($fields, $as, 'long');
    }

    /**
     * Return the lowest value of multiple columns in one row.
     *
     * @param array|string[] $fields
     * @param string         $as The output name
     *
     * @return $this
     */
    public function doubleLeast(array $fields, string $as)
    {
        return $this->least($fields, $as, 'double');
    }

    /**
     * Applies the provided JavaScript function to the given fields. Fields are passed as arguments to the JavaScript
     * function in the given order.
     *
     * NOTE: JavaScript-based functionality is disabled by default. Please refer to the Druid JavaScript programming
     * guide for guidelines about using Druid's JavaScript functionality, including instructions on how to enable it.
     *
     * @param array|string[] $fields   The fields which should be processd by the javascript function.
     * @param string         $function The javascript function which should be applied.
     * @param string         $as       The output name
     *
     * @return $this
     * @see https://druid.apache.org/docs/latest/querying/post-aggregations.html#javascript-post-aggregator
     */
    public function javascript(array $fields, string $function, string $as)
    {
        $this->postAggregations[] = new JavaScriptPostAggregator($as, $fields, $function);

        return $this;
    }

    /**
     * The hyperUniqueCardinality post aggregator is used to wrap a hyperUnique object such that it can be used in post
     * aggregations.
     *
     * This post-aggregator will inherit the rounding behavior of the aggregator it references. Note that this
     * inheritance is only effective if you directly reference an aggregator. Going through another post-aggregator,
     * for example, will cause the user-specified rounding behavior to get lost and default to "no rounding".
     *
     * @see https://druid.apache.org/docs/latest/querying/post-aggregations.html#hyperunique-cardinality-post-aggregator
     *
     * @param string $hyperUniqueField The name field value of the hyperUnique aggregator
     * @param string $as               The output name
     *
     * @return $this
     */
    public function hyperUniqueCardinality(string $hyperUniqueField, string $as)
    {
        $this->postAggregations[] = new HyperUniqueCardinalityPostAggregator($as, $hyperUniqueField);

        return $this;
    }

    /**
     * @return array|\Level23\Druid\PostAggregations\PostAggregatorInterface[]
     */
    public function getPostAggregations(): array
    {
        return $this->postAggregations;
    }
}