<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Closure;
use InvalidArgumentException;
use Level23\Druid\PostAggregations\LeastPostAggregator;
use Level23\Druid\Collections\PostAggregationCollection;
use Level23\Druid\PostAggregations\ConstantPostAggregator;
use Level23\Druid\PostAggregations\GreatestPostAggregator;
use Level23\Druid\PostAggregations\PostAggregationsBuilder;
use Level23\Druid\PostAggregations\PostAggregatorInterface;
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
     * Build our input field for the post aggregation.
     * This array can contain:
     *  - A string, referring to a metric or dimension in the query
     *  - A Closure, which allows you to build another postAggretator
     *
     * @param array $fields
     *
     * @return PostAggregationCollection
     * @throws InvalidArgumentException
     */
    protected function buildFields(array $fields): PostAggregationCollection
    {
        $first = reset($fields);

        if (is_array($first)) {
            $fields = $first;
        }

        $collection = new PostAggregationCollection();

        foreach ($fields as $field) {
            if (is_string($field)) {
                $collection->add(new FieldAccessPostAggregator($field, $field));
            } elseif ($field instanceof PostAggregatorInterface) {
                $collection->add($field);
            } elseif ($field instanceof Closure) {
                $builder = new PostAggregationsBuilder();
                call_user_func($field, $builder);
                $postAggregations = $builder->getPostAggregations();

                $collection->add(...$postAggregations);
            } else {
                throw new InvalidArgumentException(
                    'Incorrect field type given in postAggregation fields. Only strings (which will become' .
                    'FieldAccess types), Objects of the type PostAggregatorInterface and Closure\'s are allowed!'
                );
            }
        }

        return $collection;
    }

    /**
     * Divide two or more fields with each other.
     *
     * @param string               $as                The name which will be used in the output
     * @param string|Closure|array ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                we assume that it refers to another field in the query. If you
     *                                                give a closure, it will receive an instance of the
     *                                                PostAggregationsBuilder. With this builder you can build other
     *                                                post-aggregations or use constants as input for this method.
     *
     * @return $this
     */
    public function divide(string $as, ...$fieldOrClosure)
    {
        $this->postAggregations[] = new ArithmeticPostAggregator(
            $as,
            '/',
            $this->buildFields($fieldOrClosure),
            true
        );

        return $this;
    }

    /**
     * Multiply two or more fields with each other.
     *
     * @param string               $as                The name which will be used in the output
     * @param string|Closure|array ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                we assume that it refers to another field in the query. If you
     *                                                give a closure, it will receive an instance of the
     *                                                PostAggregationsBuilder. With this builder you can build other
     *                                                post-aggregations or use constants as input for this method.
     *
     * @return $this
     */
    public function multiply(string $as, ...$fieldOrClosure)
    {
        $this->postAggregations[] = new ArithmeticPostAggregator(
            $as,
            '*',
            $this->buildFields($fieldOrClosure),
            true
        );

        return $this;
    }

    /**
     * Subtract two or more fields with each other.
     *
     * @param string               $as                The name which will be used in the output
     * @param string|Closure|array ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                we assume that it refers to another field in the query. If you
     *                                                give a closure, it will receive an instance of the
     *                                                PostAggregationsBuilder. With this builder you can build other
     *                                                post-aggregations or use constants as input for this method.
     *
     * @return $this
     */
    public function subtract(string $as, ...$fieldOrClosure)
    {

        $this->postAggregations[] = new ArithmeticPostAggregator(
            $as,
            '-',
            $this->buildFields($fieldOrClosure),
            true
        );

        return $this;
    }

    /**
     * Add two or more fields with each other.
     *
     * @param string               $as                The name which will be used in the output
     * @param string|Closure|array ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                we assume that it refers to another field in the query. If you
     *                                                give a closure, it will receive an instance of the
     *                                                PostAggregationsBuilder. With this builder you can build other
     *                                                post-aggregations or use constants as input for this method.
     *
     * @return $this
     */
    public function add(string $as, ...$fieldOrClosure)
    {
        $this->postAggregations[] = new ArithmeticPostAggregator(
            $as,
            '+',
            $this->buildFields($fieldOrClosure),
            true
        );

        return $this;
    }

    /**
     * Return the quotient of two or more fields.
     *
     * @param string               $as                The name which will be used in the output
     * @param string|Closure|array ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                we assume that it refers to another field in the query. If you
     *                                                give a closure, it will receive an instance of the
     *                                                PostAggregationsBuilder. With this builder you can build other
     *                                                post-aggregations or use constants as input for this method.
     *
     * @return $this
     */
    public function quotient(string $as, ...$fieldOrClosure)
    {
        $this->postAggregations[] = new ArithmeticPostAggregator(
            $as,
            'quotient',
            $this->buildFields($fieldOrClosure),
            true
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
    public function fieldAccess(string $aggregatorOutputName, string $as = '', bool $finalizing = false)
    {
        $this->postAggregations[] = new FieldAccessPostAggregator(
            $aggregatorOutputName,
            ($as ?: $aggregatorOutputName),
            $finalizing
        );

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
     * Return the highest value of multiple columns in one row.
     *
     * @param string               $as                The name which will be used in the output
     * @param string|Closure|array ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                we assume that it refers to another field in the query. If you
     *                                                give a closure, it will receive an instance of the
     *                                                PostAggregationsBuilder. With this builder you can build other
     *                                                post-aggregations or use constants as input for this method.
     *
     * @return $this
     */
    public function longGreatest(string $as, ...$fieldOrClosure)
    {
        $this->postAggregations[] = new GreatestPostAggregator(
            $as,
            $this->buildFields($fieldOrClosure),
            'long'
        );

        return $this;
    }

    /**
     * Return the highest value of multiple columns in one row.
     *
     * @param string               $as                The name which will be used in the output
     * @param string|Closure|array ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                we assume that it refers to another field in the query. If you
     *                                                give a closure, it will receive an instance of the
     *                                                PostAggregationsBuilder. With this builder you can build other
     *                                                post-aggregations or use constants as input for this method.
     *
     * @return $this
     */
    public function doubleGreatest(string $as, ...$fieldOrClosure)
    {
        $this->postAggregations[] = new GreatestPostAggregator(
            $as,
            $this->buildFields($fieldOrClosure),
            'double'
        );

        return $this;
    }

    /**
     * Return the lowest value of multiple columns in one row.
     *
     * @param string               $as                The name which will be used in the output
     * @param string|Closure|array ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                we assume that it refers to another field in the query. If you
     *                                                give a closure, it will receive an instance of the
     *                                                PostAggregationsBuilder. With this builder you can build other
     *                                                post-aggregations or use constants as input for this method.
     *
     * @return $this
     */
    public function longLeast(string $as, ...$fieldOrClosure)
    {
        $this->postAggregations[] = new LeastPostAggregator(
            $as,
            $this->buildFields($fieldOrClosure),
            'long'
        );

        return $this;
    }

    /**
     * Return the lowest value of multiple columns in one row.
     *
     * @param string               $as                The name which will be used in the output
     * @param string|Closure|array ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                we assume that it refers to another field in the query. If you
     *                                                give a closure, it will receive an instance of the
     *                                                PostAggregationsBuilder. With this builder you can build other
     *                                                post-aggregations or use constants as input for this method.
     *
     * @return $this
     */
    public function doubleLeast(string $as, ...$fieldOrClosure)
    {
        $this->postAggregations[] = new LeastPostAggregator(
            $as,
            $this->buildFields($fieldOrClosure),
            'double'
        );

        return $this;
    }

    /**
     * This Post Aggregation function applies the provided JavaScript function to the given fields. Fields are passed
     * as arguments to the JavaScript function in the given order.
     *
     * NOTE: JavaScript-based functionality is disabled by default. Please refer to the Druid JavaScript programming
     * guide for guidelines about using Druid's JavaScript functionality, including instructions on how to enable it.
     *
     * @param string               $as                The output name
     * @param string               $function          The javascript function which should be applied.
     * @param string|Closure|array ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                we assume that it refers to another field in the query. If you
     *                                                give a closure, it will receive an instance of the
     *                                                PostAggregationsBuilder. With this builder you can build other
     *                                                post-aggregations or use constants as input for this method.
     *
     * @return $this
     * @see https://druid.apache.org/docs/latest/querying/post-aggregations.html#javascript-post-aggregator
     */
    public function postJavascript(string $as, string $function, ...$fieldOrClosure)
    {
        $this->postAggregations[] = new JavaScriptPostAggregator(
            $as,
            $this->buildFields($fieldOrClosure),
            $function
        );

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