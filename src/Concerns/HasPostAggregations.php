<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Closure;
use InvalidArgumentException;
use Level23\Druid\Types\DataType;
use Level23\Druid\PostAggregations\CdfPostAggregator;
use Level23\Druid\PostAggregations\RankPostAggregator;
use Level23\Druid\PostAggregations\LeastPostAggregator;
use Level23\Druid\Collections\PostAggregationCollection;
use Level23\Druid\PostAggregations\ConstantPostAggregator;
use Level23\Druid\PostAggregations\GreatestPostAggregator;
use Level23\Druid\PostAggregations\QuantilePostAggregator;
use Level23\Druid\PostAggregations\PostAggregationsBuilder;
use Level23\Druid\PostAggregations\PostAggregatorInterface;
use Level23\Druid\PostAggregations\QuantilesPostAggregator;
use Level23\Druid\PostAggregations\HistogramPostAggregator;
use Level23\Druid\PostAggregations\ArithmeticPostAggregator;
use Level23\Druid\PostAggregations\ExpressionPostAggregator;
use Level23\Druid\PostAggregations\JavaScriptPostAggregator;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;
use Level23\Druid\PostAggregations\SketchSummaryPostAggregator;
use Level23\Druid\PostAggregations\HyperUniqueCardinalityPostAggregator;

trait HasPostAggregations
{
    /**
     * @var \Level23\Druid\PostAggregations\PostAggregatorInterface[]
     */
    protected array $postAggregations = [];

    /**
     * Build our input field for the post aggregation.
     * This array can contain:
     *  - A string, referring to a metric or dimension in the query
     *  - A Closure, which allows you to build another postAggregator
     *
     * @param array<string|Closure|PostAggregatorInterface|string[]> $fields
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
     * @param string                  $as                The name which will be used in the output
     * @param string|Closure|string[] ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                   we assume that it refers to another field in the query. If you
     *                                                   give a closure, it will receive an instance of the
     *                                                   PostAggregationsBuilder. With this builder you can build other
     *                                                   post-aggregations or use constants as input for this method.
     *
     * @return $this
     */
    public function divide(string $as, ...$fieldOrClosure): self
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
     * This returns an approximation to the value that would be preceded by a given fraction of a hypothetical sorted
     * version of the input stream.
     *
     * To use this aggregator, make sure you include the extension in your druid server config file:
     *
     * druid.extensions.loadList=["druid-datasketches"]
     *
     * @param string         $as             The name which will be used in the output
     * @param Closure|string $fieldOrClosure Field which will be used that refers to a DoublesSketch  (fieldAccess or
     *                                       another post aggregator). When a string is given, we assume that it refers
     *                                       to another field in the query. If you give a closure, it will receive an
     *                                       instance of the PostAggregationsBuilder. With this builder you can build
     *                                       another post-aggregation or use constants as input for this method.
     * @param float          $fraction       Fractional position in the hypothetical sorted stream, number from 0 to 1
     *                                       inclusive
     *
     * @return $this
     */
    public function quantile(string $as, Closure|string $fieldOrClosure, float $fraction): self
    {
        $fields = $this->buildFields([$fieldOrClosure]);
        if ($fields->count() != 1 || !$fields[0]) {
            throw new InvalidArgumentException('You can only provide one post-aggregation, field access or constant as input field');
        }

        $this->postAggregations[] = new QuantilePostAggregator(
            $fields[0],
            $as,
            $fraction
        );

        return $this;
    }

    /**
     * This returns an approximation to the value that would be preceded by a given fraction of a hypothetical sorted
     * version of the input stream. This returns an array of quantiles corresponding to a given array of fractions.
     *
     * To use this aggregator, make sure you include the extension in your druid server config file:
     *
     * druid.extensions.loadList=["druid-datasketches"]
     *
     * @param string         $as             The name which will be used in the output
     * @param Closure|string $fieldOrClosure Field which will be used that refers to a DoublesSketch  (fieldAccess or
     *                                       another post aggregator). When a string is given, we assume that it refers
     *                                       to another field in the query. If you give a closure, it will receive an
     *                                       instance of the PostAggregationsBuilder. With this builder you can build
     *                                       another post-aggregation or use constants as input for this method.
     * @param float[]        $fractions      Array of Fractional positions in the hypothetical sorted stream, number
     *                                       from 0 to 1 inclusive
     *
     * @return $this
     */
    public function quantiles(string $as, Closure|string $fieldOrClosure, array $fractions): self
    {
        $fields = $this->buildFields([$fieldOrClosure]);
        if ($fields->count() != 1 || !$fields[0]) {
            throw new InvalidArgumentException('You can only provide one post-aggregation, field access or constant as input field');
        }

        $this->postAggregations[] = new QuantilesPostAggregator(
            $fields[0],
            $as,
            $fractions
        );

        return $this;
    }

    /**
     * This returns an approximation to the histogram given an array of split points that define the histogram bins or
     * a number of bins (not both). An array of m unique, monotonically increasing split points divide the real number
     * line into m+1 consecutive disjoint intervals. The definition of an interval is inclusive of the left split point
     * and exclusive of the right split point. If the number of bins is specified instead of split points, the interval
     * between the minimum and maximum values is divided into the given number of equally-spaced bins.
     *
     * To use this aggregator, make sure you include the extension in your druid server config file:
     *
     * druid.extensions.loadList=["druid-datasketches"]
     *
     * @param string                $as             The name which will be used in the output
     * @param Closure|string        $fieldOrClosure Field which will be used that refers to a DoublesSketch
     *                                              (fieldAccess or another post aggregator). When a string is given,
     *                                              we assume that it refers to another field in the query. If you give
     *                                              a closure, it will receive an instance of the
     *                                              PostAggregationsBuilder. With this builder you can build another
     *                                              post-aggregation or use constants as input for this method.
     * @param array<int|float>|null $splitPoints    Array of split points (optional)
     * @param int|null              $numBins        Number of bins (optional, defaults to 10)
     *
     * @return $this
     */
    public function histogram(string $as, Closure|string $fieldOrClosure, ?array $splitPoints = null, ?int $numBins = null): self
    {
        $fields = $this->buildFields([$fieldOrClosure]);
        if ($fields->count() != 1 || !$fields[0]) {
            throw new InvalidArgumentException('You can only provide one post-aggregation, field access or constant as input field');
        }

        $this->postAggregations[] = new HistogramPostAggregator(
            $fields[0],
            $as,
            $splitPoints,
            $numBins
        );

        return $this;
    }

    /**
     * This returns an approximation to the rank of a given value that is the fraction of the distribution less than
     * that value.
     *
     * To use this aggregator, make sure you include the extension in your druid server config file:
     *
     * druid.extensions.loadList=["druid-datasketches"]
     *
     * @param string         $as             The name which will be used in the output
     * @param Closure|string $fieldOrClosure Field which will be used that refers to a DoublesSketch  (fieldAccess or
     *                                       another post aggregator). When a string is given, we assume that it refers
     *                                       to another field in the query. If you give a closure, it will receive an
     *                                       instance of the PostAggregationsBuilder. With this builder you can build
     *                                       another post-aggregation or use constants as input for this method.
     * @param float|int      $value          This returns an approximation to the rank of a given value that is the
     *                                       fraction of the distribution less than that value.
     *
     * @return $this
     */
    public function rank(string $as, Closure|string $fieldOrClosure, float|int $value): self
    {
        $fields = $this->buildFields([$fieldOrClosure]);
        if ($fields->count() != 1 || !$fields[0]) {
            throw new InvalidArgumentException('You can only provide one post-aggregation, field access or constant as input field');
        }

        $this->postAggregations[] = new RankPostAggregator(
            $fields[0],
            $as,
            $value
        );

        return $this;
    }

    /**
     * This returns an approximation to the Cumulative Distribution Function given an array of split points that define
     * the edges of the bins. An array of m unique, monotonically increasing split points divide the real number line
     * into m+1 consecutive disjoint intervals. The definition of an interval is inclusive of the left split point and
     * exclusive of the right split point. The resulting array of fractions can be viewed as ranks of each split point
     * with one additional rank that is always 1.
     *
     * To use this aggregator, make sure you include the extension in your druid server config file:
     *
     * druid.extensions.loadList=["druid-datasketches"]
     *
     * @param string         $as             The name which will be used in the output
     * @param Closure|string $fieldOrClosure Field which will be used that refers to a DoublesSketch  (fieldAccess or
     *                                       another post aggregator). When a string is given, we assume that it refers
     *                                       to another field in the query. If you give a closure, it will receive an
     *                                       instance of the PostAggregationsBuilder. With this builder you can build
     *                                       another post-aggregation or use constants as input for this method.
     * @param float[]        $splitPoints    Array of split points
     *
     * @return $this
     */
    public function cdf(string $as, Closure|string $fieldOrClosure, array $splitPoints): self
    {
        $fields = $this->buildFields([$fieldOrClosure]);
        if ($fields->count() != 1 || !$fields[0]) {
            throw new InvalidArgumentException('You can only provide one post-aggregation, field access or constant as input field');
        }

        $this->postAggregations[] = new CdfPostAggregator(
            $fields[0],
            $as,
            $splitPoints
        );

        return $this;
    }

    /**
     * This returns a summary of the sketch that can be used for debugging. This is the result of calling toString()
     * method.
     *
     * To use this aggregator, make sure you include the extension in your druid server config file:
     *
     * druid.extensions.loadList=["druid-datasketches"]
     *
     * @param string         $as             The name which will be used in the output
     * @param Closure|string $fieldOrClosure Field which will be used that refers to a DoublesSketch  (fieldAccess or
     *                                       another post aggregator). When a string is given, we assume that it refers
     *                                       to another field in the query. If you give a closure, it will receive an
     *                                       instance of the PostAggregationsBuilder. With this builder you can build
     *                                       another post-aggregation or use constants as input for this method.
     *
     * @return $this
     */
    public function sketchSummary(string $as, Closure|string $fieldOrClosure): self
    {
        $fields = $this->buildFields([$fieldOrClosure]);
        if ($fields->count() != 1 || !$fields[0]) {
            throw new InvalidArgumentException('You can only provide one post-aggregation, field access or constant as input field');
        }

        $this->postAggregations[] = new SketchSummaryPostAggregator(
            $fields[0],
            $as
        );

        return $this;
    }

    /**
     * Multiply two or more fields with each other.
     *
     * @param string                  $as                The name which will be used in the output
     * @param string|Closure|string[] ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                   we assume that it refers to another field in the query. If you
     *                                                   give a closure, it will receive an instance of the
     *                                                   PostAggregationsBuilder. With this builder you can build other
     *                                                   post-aggregations or use constants as input for this method.
     *
     * @return $this
     */
    public function multiply(string $as, ...$fieldOrClosure): self
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
     * @param string                  $as                The name which will be used in the output
     * @param string|Closure|string[] ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                   we assume that it refers to another field in the query. If you
     *                                                   give a closure, it will receive an instance of the
     *                                                   PostAggregationsBuilder. With this builder you can build other
     *                                                   post-aggregations or use constants as input for this method.
     *
     * @return $this
     */
    public function subtract(string $as, ...$fieldOrClosure): self
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
     * @param string                  $as                The name which will be used in the output
     * @param string|Closure|string[] ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                   we assume that it refers to another field in the query. If you
     *                                                   give a closure, it will receive an instance of the
     *                                                   PostAggregationsBuilder. With this builder you can build other
     *                                                   post-aggregations or use constants as input for this method.
     *
     * @return $this
     */
    public function add(string $as, ...$fieldOrClosure): self
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
     * @param string                  $as                The name which will be used in the output
     * @param string|Closure|string[] ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                   we assume that it refers to another field in the query. If you
     *                                                   give a closure, it will receive an instance of the
     *                                                   PostAggregationsBuilder. With this builder you can build other
     *                                                   post-aggregations or use constants as input for this method.
     *
     * @return $this
     */
    public function quotient(string $as, ...$fieldOrClosure): self
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
     * @param string $aggregatorOutputName This refers to the output name of the aggregator given in the aggregations
     *                                     portion of the query
     * @param string $as                   The output name as how we can access it
     * @param bool   $finalizing           Set this to true if you want to return a finalized value, such as an
     *                                     estimated cardinality.
     *
     * @return $this
     */
    public function fieldAccess(string $aggregatorOutputName, string $as = '', bool $finalizing = false): self
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
     * @param float|int $numericValue This will be our static value
     * @param string    $as           The output name as how we can access it
     *
     * @return $this
     */
    public function constant(float|int $numericValue, string $as): self
    {
        $this->postAggregations[] = new ConstantPostAggregator($as, $numericValue);

        return $this;
    }

    /**
     * Return the highest value of multiple columns in one row.
     *
     * @param string                  $as                The name which will be used in the output
     * @param string|Closure|string[] ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                   we assume that it refers to another field in the query. If you
     *                                                   give a closure, it will receive an instance of the
     *                                                   PostAggregationsBuilder. With this builder you can build other
     *                                                   post-aggregations or use constants as input for this method.
     *
     * @return $this
     */
    public function longGreatest(string $as, ...$fieldOrClosure): self
    {
        $this->postAggregations[] = new GreatestPostAggregator(
            $as,
            $this->buildFields($fieldOrClosure),
            DataType::LONG
        );

        return $this;
    }

    /**
     * Return the highest value of multiple columns in one row.
     *
     * @param string                  $as                The name which will be used in the output
     * @param string|Closure|string[] ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                   we assume that it refers to another field in the query. If you
     *                                                   give a closure, it will receive an instance of the
     *                                                   PostAggregationsBuilder. With this builder you can build other
     *                                                   post-aggregations or use constants as input for this method.
     *
     * @return $this
     */
    public function doubleGreatest(string $as, ...$fieldOrClosure): self
    {
        $this->postAggregations[] = new GreatestPostAggregator(
            $as,
            $this->buildFields($fieldOrClosure),
            DataType::DOUBLE
        );

        return $this;
    }

    /**
     * Return the lowest value of multiple columns in one row.
     *
     * @param string                  $as                The name which will be used in the output
     * @param string|Closure|string[] ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                   we assume that it refers to another field in the query. If you
     *                                                   give a closure, it will receive an instance of the
     *                                                   PostAggregationsBuilder. With this builder you can build other
     *                                                   post-aggregations or use constants as input for this method.
     *
     * @return $this
     */
    public function longLeast(string $as, ...$fieldOrClosure): self
    {
        $this->postAggregations[] = new LeastPostAggregator(
            $as,
            $this->buildFields($fieldOrClosure),
            DataType::LONG
        );

        return $this;
    }

    /**
     * Return the lowest value of multiple columns in one row.
     *
     * @param string                  $as                The name which will be used in the output
     * @param string|Closure|string[] ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                   we assume that it refers to another field in the query. If you
     *                                                   give a closure, it will receive an instance of the
     *                                                   PostAggregationsBuilder. With this builder you can build other
     *                                                   post-aggregations or use constants as input for this method.
     *
     * @return $this
     */
    public function doubleLeast(string $as, ...$fieldOrClosure): self
    {
        $this->postAggregations[] = new LeastPostAggregator(
            $as,
            $this->buildFields($fieldOrClosure),
            DataType::DOUBLE
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
     * @param string                  $as                The output name
     * @param string                  $function          The javascript function which should be applied.
     * @param string|Closure|string[] ...$fieldOrClosure One or more fields which will be used. When a string is given,
     *                                                   we assume that it refers to another field in the query. If you
     *                                                   give a closure, it will receive an instance of the
     *                                                   PostAggregationsBuilder. With this builder you can build other
     *                                                   post-aggregations or use constants as input for this method.
     *
     * @return $this
     * @see https://druid.apache.org/docs/latest/querying/post-aggregations.html#javascript-post-aggregator
     */
    public function postJavascript(string $as, string $function, ...$fieldOrClosure): self
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
     * @param string      $hyperUniqueField The name field value of the hyperUnique aggregator
     * @param string|null $as               The output name
     *
     * @return $this
     */
    public function hyperUniqueCardinality(string $hyperUniqueField, string $as = null): self
    {
        $this->postAggregations[] = new HyperUniqueCardinalityPostAggregator($hyperUniqueField, $as);

        return $this;
    }

    /**
     * @return array|\Level23\Druid\PostAggregations\PostAggregatorInterface[]
     */
    public function getPostAggregations(): array
    {
        return $this->postAggregations;
    }

    public function expression(string $as, $expression): self
    {
        $this->postAggregations[] = new ExpressionPostAggregator(
            $as,
            $expression
        );

        return $this;
    }
}
