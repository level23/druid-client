<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Closure;
use InvalidArgumentException;
use Level23\Druid\Filters\InFilter;
use Level23\Druid\Filters\OrFilter;
use Level23\Druid\Filters\AndFilter;
use Level23\Druid\Filters\NotFilter;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Filters\LikeFilter;
use Level23\Druid\Filters\BoundFilter;
use Level23\Druid\Filters\RegexFilter;
use Level23\Druid\Filters\SearchFilter;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Filters\FilterBuilder;
use Level23\Druid\Filters\BetweenFilter;
use Level23\Druid\Filters\IntervalFilter;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Filters\JavascriptFilter;
use Level23\Druid\Interval\IntervalInterface;
use Level23\Druid\Dimensions\DimensionBuilder;
use Level23\Druid\Extractions\ExtractionBuilder;
use Level23\Druid\Dimensions\DimensionInterface;
use Level23\Druid\Filters\ColumnComparisonFilter;
use Level23\Druid\Extractions\ExtractionInterface;

trait HasFilter
{
    /**
     * @var \Level23\Druid\Filters\FilterInterface|null
     */
    protected $filter;

    /**
     * Filter our results where the given dimension matches the value based on the operator.
     * The operator can be '=', '>', '>=', '<', '<=', '<>', '!=', 'like', 'not like', 'regex', 'not regex',
     * 'javascript', 'not javascript', 'search' and 'not search'
     *
     * @param string|FilterInterface|\Closure $filterOrDimensionOrClosure The dimension which you want to filter.
     * @param string|null                     $operator                   The operator which you want to use to filter.
     *                                                                    See below for a complete list of supported
     *                                                                    operators.
     * @param mixed                           $value                      The value which you want to use in your
     *                                                                    filter comparison
     * @param \Closure|null                   $extraction                 A closure which builds one or more extraction
     *                                                                    function. These are applied before the filter
     *                                                                    will be applied. So the filter will use the
     *                                                                    value returned by the extraction function(s).
     * @param string                          $boolean                    This influences how this filter will be
     *                                                                    joined with previous added filters. Should
     *                                                                    both filters apply ("and") or one or the
     *                                                                    other ("or") ? Default is "and".
     *
     * @return $this
     */
    public function where(
        $filterOrDimensionOrClosure,
        $operator = null,
        $value = null,
        Closure $extraction = null,
        string $boolean = 'and'
    ) {
        $filter = null;
        if (is_string($filterOrDimensionOrClosure)) {
            if ($value === null && $operator !== null) {
                $value    = $operator;
                $operator = '=';
            }

            if ($operator === null || $value === null) {
                throw new InvalidArgumentException('You have to supply an operator and an compare value when you supply a dimension as string');
            }

            $operator = strtolower($operator);

            if ($operator == '=') {
                $filter = new SelectorFilter(
                    $filterOrDimensionOrClosure,
                    (string)$value,
                    $this->getExtraction($extraction)
                );
            } elseif ($operator == '<>' || $operator == '!=') {
                $filter = new NotFilter(
                    new SelectorFilter($filterOrDimensionOrClosure, (string)$value, $this->getExtraction($extraction))
                );
            } elseif (in_array($operator, ['>', '>=', '<', '<='])) {
                $filter = new BoundFilter(
                    $filterOrDimensionOrClosure,
                    $operator,
                    (string)$value,
                    null,
                    $this->getExtraction($extraction)
                );
            } elseif ($operator == 'like') {
                $filter = new LikeFilter(
                    $filterOrDimensionOrClosure, $value, '\\', $this->getExtraction($extraction)
                );
            } elseif ($operator == 'not like') {
                $filter = new NotFilter(
                    new LikeFilter($filterOrDimensionOrClosure, $value, '\\', $this->getExtraction($extraction))
                );
            } elseif ($operator == 'javascript') {
                $filter = new JavascriptFilter($filterOrDimensionOrClosure, $value, $this->getExtraction($extraction));
            } elseif ($operator == 'not javascript') {
                $filter = new NotFilter(
                    new JavascriptFilter($filterOrDimensionOrClosure, $value, $this->getExtraction($extraction))
                );
            } elseif ($operator == 'regex' || $operator == 'regexp') {
                $filter = new RegexFilter($filterOrDimensionOrClosure, $value, $this->getExtraction($extraction));
            } elseif ($operator == 'not regex' || $operator == 'not regexp') {
                $filter = new NotFilter(
                    new RegexFilter($filterOrDimensionOrClosure, $value, $this->getExtraction($extraction))
                );
            } elseif ($operator == 'search') {
                $filter = new SearchFilter(
                    $filterOrDimensionOrClosure, $value, false, $this->getExtraction($extraction)
                );
            } elseif ($operator == 'not search') {
                $filter = new NotFilter(new SearchFilter(
                    $filterOrDimensionOrClosure, $value, false, $this->getExtraction($extraction)
                ));
            } else {
                $filter = null;
            }
        } elseif ($filterOrDimensionOrClosure instanceof FilterInterface) {
            $filter = $filterOrDimensionOrClosure;
        } elseif ($filterOrDimensionOrClosure instanceof Closure) {

            // lets create a bew builder object where the user can mess around with
            $builder = new FilterBuilder();

            // call the user function
            call_user_func($filterOrDimensionOrClosure, $builder);

            // Now retrieve the filter which was created and add it to our current filter set.
            $filter = $builder->getFilter();
        }

        if ($filter === null) {
            throw new InvalidArgumentException('The arguments which you have supplied cannot be parsed.');
        }

        strtolower($boolean) == 'and' ? $this->addAndFilter($filter) : $this->addOrFilter($filter);

        return $this;
    }

    /**
     * Build a where selection which is inverted
     *
     * @param \Closure $filterBuilder A closure which will receive a FilterBuilder instance.
     * @param string   $boolean       This influences how this filter will be joined with previous added filters.
     *                                Should both filters apply ("and") or one or the other ("or") ? Default is "and".
     *
     * @return $this
     */
    public function whereNot(Closure $filterBuilder, $boolean = 'and')
    {
        // lets create a bew builder object where the user can mess around with
        $builder = new FilterBuilder();

        // call the user function
        call_user_func($filterBuilder, $builder);

        // Now retrieve the filter which was created and add it to our current filter set.
        $filter = $builder->getFilter();
        if ($filter) {
            return $this->where(new NotFilter($filter), null, null, null, $boolean);
        }

        // Whe no filter was given, just return.
        return $this;
    }

    /**
     * Build a where selection which is inverted
     *
     * @param \Closure $filterBuilder A closure which will receive a FilterBuilder instance.
     *
     * @return $this
     */
    public function orWhereNot(Closure $filterBuilder)
    {
        return $this->whereNot($filterBuilder, 'or');
    }

    /**
     * This applies a filter, only it will join previous added filters with an "or" instead of an "and".
     * See the documentation of the "where" method for more information
     *
     * @param string|FilterInterface $filterOrDimension
     * @param string|null            $operator
     * @param mixed|null             $value
     * @param \Closure|null          $extraction
     *
     * @return $this
     * @see \Level23\Druid\Concerns\HasFilter::where()
     */
    public function orWhere($filterOrDimension, $operator = null, $value = null, Closure $extraction = null)
    {
        return $this->where($filterOrDimension, $operator, $value, $extraction, 'or');
    }

    /**
     * Filter records where the given dimension exists in the given list of items
     *
     * @param string        $dimension  The dimension which you want to filte
     * @param array         $items      A list of values. We will return records where the dimension is in this list.
     * @param \Closure|null $extraction An extraction function to extract a different value from the dimension.
     * @param string        $boolean    This influences how this filter will be joined with previous added filters.
     *                                  Should both filters apply ("and") or one or the other ("or") ? Default is
     *                                  "and".
     *
     * @return $this
     */
    public function whereIn(string $dimension, array $items, Closure $extraction = null, $boolean = 'and')
    {
        $filter = new InFilter($dimension, $items, $this->getExtraction($extraction));

        return $this->where($filter, null, null, null, $boolean);
    }

    /**
     * Filter records where the given dimension exists in the given list of items.
     *
     * If there are previously defined filters, this filter will be joined with an "or".
     *
     * @param string        $dimension  The dimension which you want to filte
     * @param array         $items      A list of values. We will return records where the dimension is in this list.
     * @param \Closure|null $extraction An extraction function to extract a different value from the dimension.
     *
     * @return $this
     */
    public function orWhereIn(string $dimension, array $items, Closure $extraction = null)
    {
        return $this->whereIn($dimension, $items, $extraction, 'or');
    }

    /**
     * Filter records where dimensionA is equal to dimensionB.
     * You can either supply a string or a Closure. The Closure will receive a DimensionBuilder object, which allows
     * you to select a dimension and apply extraction functions if needed.
     *
     * Example:
     * ```php
     * $builder->whereColumn('initials', function(DimensionBuilder $dimensionBuilder) {
     *   $dimensionBuilder->select('first_name', function(ExtractionBuilder $extractionBuilder) {
     *     $extractionBuilder->substring(0, 1);
     *   });
     * });
     * ```
     *
     * @param string|Closure $dimensionA The dimension which you want to compare, or a Closure which will receive a
     *                                   DimensionBuilder which allows you to select a dimension in a more advance way.
     * @param string|Closure $dimensionB The dimension which you want to compare, or a Closure which will receive a
     *                                   DimensionBuilder which allows you to select a dimension in a more advance way.
     * @param string         $boolean    This influences how this filter will be joined with previous added filters.
     *                                   Should both filters apply ("and") or one or the other ("or") ? Default is
     *                                   "and".
     *
     * @return $this
     */
    public function whereColumn($dimensionA, $dimensionB, string $boolean = 'and')
    {
        $filter = new ColumnComparisonFilter(
            $this->columnCompareDimension($dimensionA),
            $this->columnCompareDimension($dimensionB)
        );

        return $this->where($filter, null, null, null, $boolean);
    }

    /**
     * Filter records where dimensionA is equal to dimensionB.
     * You can either supply a string or a Closure. The Closure will receive a DimensionBuilder object, which allows
     * you to select a dimension and apply extraction functions if needed.
     *
     * Example:
     * ```php
     * $builder->orWhereColumn('initials', function(DimensionBuilder $dimensionBuilder) {
     *   $dimensionBuilder->select('first_name', function(ExtractionBuilder $extractionBuilder) {
     *     $extractionBuilder->substring(0, 1);
     *   });
     * });
     * ```
     *
     * @param string|Closure $dimensionA The dimension which you want to compare, or a Closure which will receive a
     *                                   DimensionBuilder which allows you to select a dimension in a more advance way.
     * @param string|Closure $dimensionB The dimension which you want to compare, or a Closure which will receive a
     *                                   DimensionBuilder which allows you to select a dimension in a more advance way.
     *
     * @return $this
     */
    public function orWhereColumn($dimensionA, $dimensionB)
    {
        return $this->whereColumn($dimensionA, $dimensionB, 'or');
    }

    /**
     * Filter records where dimensionA is NOT equal to dimensionB.
     * You can either supply a string or a Closure. The Closure will receive a DimensionBuilder object, which allows
     * you to select a dimension and apply extraction functions if needed.
     *
     * Example:
     * ```php
     * $builder->whereNotColumn('initials', function(DimensionBuilder $dimensionBuilder) {
     *   $dimensionBuilder->select('first_name', function(ExtractionBuilder $extractionBuilder) {
     *     $extractionBuilder->substring(0, 1);
     *   });
     * });
     * ```
     *
     * @param string|Closure $dimensionA The dimension which you want to compare, or a Closure which will receive a
     *                                   DimensionBuilder which allows you to select a dimension in a more advance way.
     * @param string|Closure $dimensionB The dimension which you want to compare, or a Closure which will receive a
     *                                   DimensionBuilder which allows you to select a dimension in a more advance way.
     * @param string         $boolean    This influences how this filter will be joined with previous added filters.
     *                                   Should both filters apply ("and") or one or the other ("or") ? Default is
     *                                   "and".
     *
     * @return $this
     * @deprecated Use the whereNot() method combined with a whereColumn instead.
     */
    public function whereNotColumn($dimensionA, $dimensionB, string $boolean = 'and')
    {
        $filter = new ColumnComparisonFilter(
            $this->columnCompareDimension($dimensionA),
            $this->columnCompareDimension($dimensionB)
        );

        return $this->where(new NotFilter($filter), null, null, null, $boolean);
    }

    /**
     * Filter records where dimensionA is NOT equal to dimensionB.
     * You can either supply a string or a Closure. The Closure will receive a DimensionBuilder object, which allows
     * you to select a dimension and apply extraction functions if needed.
     *
     * Example:
     * ```php
     * $builder->orWhereNotColumn('initials', function(DimensionBuilder $dimensionBuilder) {
     *   $dimensionBuilder->select('first_name', function(ExtractionBuilder $extractionBuilder) {
     *     $extractionBuilder->substring(0, 1);
     *   });
     * });
     * ```
     *
     * @param string|Closure $dimensionA The dimension which you want to compare, or a Closure which will receive a
     *                                   DimensionBuilder which allows you to select a dimension in a more advance way.
     * @param string|Closure $dimensionB The dimension which you want to compare, or a Closure which will receive a
     *                                   DimensionBuilder which allows you to select a dimension in a more advance way.
     *
     * @return $this
     */
    public function orWhereNotColumn($dimensionA, $dimensionB)
    {
        return $this->whereNot(function (FilterBuilder $builder) use ($dimensionA, $dimensionB) {
            $builder->whereColumn($dimensionA, $dimensionB);
        }, 'or');
    }

    /**
     * This filter will select records where the given dimension is greater than or equal to the given minValue, and
     * less than or equal to the given $maxValue.
     *
     * So in SQL syntax, this would be:
     * ```
     * WHERE dimension => $minValue AND dimension <= $maxValue
     * ```
     *
     * @param string        $dimension   The dimension which you want to filter
     * @param string|int    $minValue    The minimum value where the dimension should match. It should be equal or
     *                                   greater than this value.
     * @param string|int    $maxValue    The maximum value where the dimension should match. It should be less than
     *                                   this value.
     * @param \Closure|null $extraction  Extraction function to extract a different value from the dimension.
     * @param null|string   $ordering    Specifies the sorting order to use when comparing values against the
     *                                   between filter. Can be one of the following values: "lexicographic",
     *                                   "alphanumeric", "numeric", "strlen", "version". See Sorting Orders for
     *                                   more details. By default it will be "numeric" if the values are
     *                                   numeric, otherwise it will be "lexicographic"
     * @param string        $boolean     This influences how this filter will be joined with previous added filters.
     *                                   Should both filters apply ("and") or one or the other ("or") ? Default is
     *                                   "and".
     *
     * @return $this
     */
    public function whereBetween(
        string $dimension,
        $minValue,
        $maxValue,
        Closure $extraction = null,
        string $ordering = null,
        string $boolean = 'and'
    ) {
        $filter = new BetweenFilter($dimension, $minValue, $maxValue, $ordering, $this->getExtraction($extraction));

        return $this->where($filter, null, null, null, $boolean);
    }

    /**
     * This filter will select records where the given dimension is greater than or equal to the given minValue, and
     * less than or equal to the given $maxValue.
     *
     * This method will join previous added filters with an "or" instead of an "and".
     *
     * So in SQL syntax, this would be:
     * ```
     * WHERE (dimension => $minValue AND dimension <= $maxValue) or .... (other filters here)
     * ```
     *
     * @param string        $dimension   The dimension which you want to filter
     * @param string|int    $minValue    The minimum value where the dimension should match. It should be equal or
     *                                   greater than this value.
     * @param string|int    $maxValue    The maximum value where the dimension should match. It should be less than
     *                                   this value.
     * @param \Closure|null $extraction  Extraction function to extract a different value from the dimension.
     * @param null|string   $ordering    Specifies the sorting order to use when comparing values against the
     *                                   between filter. Can be one of the following values: "lexicographic",
     *                                   "alphanumeric", "numeric", "strlen", "version". See Sorting Orders for
     *                                   more details. By default it will be "numeric" if the values are
     *                                   numeric, otherwise it will be "lexicographic"
     *
     * @return $this
     */
    public function orWhereBetween(
        string $dimension,
        $minValue,
        $maxValue,
        Closure $extraction = null,
        string $ordering = null
    ) {
        return $this->whereBetween($dimension, $minValue, $maxValue, $extraction, $ordering, 'or');
    }

    /**
     * This filter will select records where the given dimension is NOT between the given min and max value.
     *
     * So in SQL syntax, this would be:
     * ```
     * WHERE dimension < $minValue AND dimension > $maxValue
     * ```
     *
     * @param string        $dimension   The dimension which you want to filter
     * @param string|int    $minValue    The minimum value where the dimension should NOT match. It should be equal or
     *                                   greater than this value.
     * @param string|int    $maxValue    The maximum value where the dimension should NOT match. It should be less than
     *                                   this value.
     * @param \Closure|null $extraction  Extraction function to extract a different value from the dimension.
     * @param null|string   $ordering    Specifies the sorting order to use when comparing values against the
     *                                   between filter. Can be one of the following values: "lexicographic",
     *                                   "alphanumeric", "numeric", "strlen", "version". See Sorting Orders for
     *                                   more details. By default it will be "numeric" if the values are
     *                                   numeric, otherwise it will be "lexicographic"
     * @param string        $boolean     This influences how this filter will be joined with previous added filters.
     *                                   Should both filters apply ("and") or one or the other ("or") ? Default is
     *                                   "and".
     *
     * @return $this
     * @deprecated Use the whereNot() method combined with a whereBetween instead.
     */
    public function whereNotBetween(
        string $dimension,
        $minValue,
        $maxValue,
        Closure $extraction = null,
        string $ordering = null,
        string $boolean = 'and'
    ) {
        $filter = new BetweenFilter($dimension, $minValue, $maxValue, $ordering, $this->getExtraction($extraction));

        return $this->where(new NotFilter($filter), null, null, null, $boolean);
    }

    /**
     * This filter will select records where the given dimension is NOT between the given min and max value.
     *
     * So in SQL syntax, this would be:
     * ```
     * WHERE (dimension < $minValue AND dimension > $maxValue) or  .... (other filters here)
     * ```
     *
     * @param string        $dimension   The dimension which you want to filter
     * @param string|int    $minValue    The minimum value where the dimension should NOT match. It should be equal or
     *                                   greater than this value.
     * @param string|int    $maxValue    The maximum value where the dimension should NOT match. It should be less than
     *                                   this value.
     * @param \Closure|null $extraction  Extraction function to extract a different value from the dimension.
     * @param null|string   $ordering    Specifies the sorting order to use when comparing values against the
     *                                   between filter. Can be one of the following values: "lexicographic",
     *                                   "alphanumeric", "numeric", "strlen", "version". See Sorting Orders for
     *                                   more details. By default it will be "numeric" if the values are
     *                                   numeric, otherwise it will be "lexicographic"
     *
     * @return $this
     * @deprecated Use the whereNot() method combined with a whereBetween instead.
     */
    public function orWhereNotBetween(
        string $dimension,
        $minValue,
        $maxValue,
        Closure $extraction = null,
        string $ordering = null
    ) {
        return $this->whereNot(function (FilterBuilder $builder) use (
            $ordering,
            $extraction,
            $maxValue,
            $minValue,
            $dimension
        ) {
            $builder->whereBetween($dimension, $minValue, $maxValue, $extraction, $ordering);
        }, 'or');
    }

    /**
     * Filter records where the given dimension NOT exists in the given list of items
     *
     * @param string        $dimension  The dimension which you want to filter
     * @param array         $items      A list of values. We will return records where the dimension is NOT in this
     *                                  list.
     * @param \Closure|null $extraction An extraction function to extract a different value from the dimension.
     * @param string        $boolean    This influences how this filter will be joined with previous added filters.
     *                                  Should both filters apply ("and") or one or the other ("or") ? Default is
     *                                  "and".
     *
     * @return $this
     * @deprecated Use the whereNot() method combined with a whereIn instead.
     */
    public function whereNotIn(string $dimension, array $items, Closure $extraction = null, string $boolean = 'and')
    {
        $filter = new NotFilter(new InFilter($dimension, $items, $this->getExtraction($extraction)));

        return $this->where($filter, null, null, null, $boolean);
    }

    /**
     * Filter records where the given dimension NOT exists in the given list of items
     *
     * @param string        $dimension  The dimension which you want to filter
     * @param array         $items      A list of values. We will return records where the dimension is NOT in this
     *                                  list.
     * @param \Closure|null $extraction An extraction function to extract a different value from the dimension.
     *
     * @return $this
     * @deprecated Use the orWhereNot() method combined with a whereIn instead.
     */
    public function orWhereNotIn(string $dimension, array $items, Closure $extraction = null)
    {
        return $this->whereNot(function (FilterBuilder $filterBuilder) use ($extraction, $items, $dimension) {
            $filterBuilder->whereIn($dimension, $items, $extraction);
        }, 'or');
    }

    /**
     * Filter on an dimension where the value does NOT exists in the given intervals array.
     *
     * The intervals array can contain the following:
     * - Only 2 elements, start and stop.
     * - an Interval object
     * - an raw interval string as used in druid. For example: 2019-04-15T08:00:00.000Z/2019-04-15T09:00:00.000Z
     * - an array which each contain 2 elements, a start and stop date. These can be an DateTime object, a unix
     * timestamp or anything which can be parsed by DateTime::__construct
     *
     * So valid are:
     * ['now', 'tomorrow']
     * [['now', 'now + 1 hour'], ['tomorrow', 'tomorrow + 1 hour']]
     * ['2019-04-15T08:00:00.000Z/2019-04-15T09:00:00.000Z']
     *
     * @param string        $dimension  The dimension which you want to filter
     * @param array         $intervals  The interval which you do not want to match. See above for more info.
     * @param \Closure|null $extraction Extraction function to extract a different value from the dimension.
     * @param string        $boolean    This influences how this filter will be joined with previous added filters.
     *                                  Should both filters apply ("and") or one or the other ("or") ? Default is
     *                                  "and".
     *
     * @return $this
     * @deprecated Use the whereNot() method combined with a whereInterval instead.
     */
    public function whereNotInterval(
        string $dimension,
        array $intervals,
        Closure $extraction = null,
        string $boolean = 'and'
    ) {
        $filter = new IntervalFilter(
            $dimension,
            $this->normalizeIntervals($intervals),
            $this->getExtraction($extraction)
        );

        return $this->where(new NotFilter($filter), null, null, null, $boolean);
    }

    /**
     * Filter on an dimension where the value does NOT exists in the given intervals array.
     *
     * The intervals array can contain the following:
     * - Only 2 elements, start and stop.
     * - an Interval object
     * - an raw interval string as used in druid. For example: 2019-04-15T08:00:00.000Z/2019-04-15T09:00:00.000Z
     * - an array which each contain 2 elements, a start and stop date. These can be an DateTime object, a unix
     * timestamp or anything which can be parsed by DateTime::__construct
     *
     * So valid are:
     * ['now', 'tomorrow']
     * [['now', 'now + 1 hour'], ['tomorrow', 'tomorrow + 1 hour']]
     * ['2019-04-15T08:00:00.000Z/2019-04-15T09:00:00.000Z']
     *
     * @param string        $dimension  The dimension which you want to filter
     * @param array         $intervals  The interval which you do not want to match. See above for more info.
     * @param \Closure|null $extraction Extraction function to extract a different value from the dimension.
     *
     * @return $this
     * @deprecated Use the orWhereNot() method combined with a whereInterval instead.
     */
    public function orWhereNotInterval(string $dimension, array $intervals, Closure $extraction = null)
    {
        return $this->whereNot(function (FilterBuilder $filterBuilder) use ($extraction, $intervals, $dimension) {
            $filterBuilder->whereInterval($dimension, $intervals, $extraction);
        }, 'or');
    }

    /**
     * Filter on an dimension where the value exists in the given intervals array.
     *
     * The intervals array can contain the following:
     * - an Interval object
     * - an raw interval string as used in druid. For example: 2019-04-15T08:00:00.000Z/2019-04-15T09:00:00.000Z
     * - an array which contains 2 elements, a start and stop date. These can be an DateTime object, a unix timestamp
     *   or anything which can be parsed by DateTime::__construct
     *
     * @param string        $dimension  The dimension which you want to filter
     * @param array         $intervals  The interval which you want to match. See above for more info.
     * @param \Closure|null $extraction Extraction function to extract a different value from the dimension.
     * @param string        $boolean    This influences how this filter will be joined with previous added filters.
     *                                  Should both filters apply ("and") or one or the other ("or") ? Default is
     *                                  "and".
     *
     * @return $this
     */
    public function whereInterval(
        string $dimension,
        array $intervals,
        Closure $extraction = null,
        string $boolean = 'and'
    ) {
        $filter = new IntervalFilter(
            $dimension,
            $this->normalizeIntervals($intervals),
            $this->getExtraction($extraction)
        );

        return $this->where($filter, null, null, null, $boolean);
    }

    /**
     * Filter on an dimension where the value exists in the given intervals array.
     *
     * The intervals array can contain the following:
     * - an Interval object
     * - an raw interval string as used in druid. For example: 2019-04-15T08:00:00.000Z/2019-04-15T09:00:00.000Z
     * - an array which contains 2 elements, a start and stop date. These can be an DateTime object, a unix timestamp
     *   or anything which can be parsed by DateTime::__construct
     *
     * @param string        $dimension  The dimension which you want to filter
     * @param array         $intervals  The interval which you want to match. See above for more info.
     * @param \Closure|null $extraction Extraction function to extract a different value from the dimension.
     *
     * @return $this
     */
    public function orWhereInterval(string $dimension, array $intervals, Closure $extraction = null)
    {
        return $this->whereInterval($dimension, $intervals, $extraction, 'or');
    }

    /**
     * Normalize the given dimension to a DimensionInterface object.
     *
     * @param string|Closure $dimension
     *
     * @return \Level23\Druid\Dimensions\DimensionInterface
     * @throws InvalidArgumentException
     */
    protected function columnCompareDimension($dimension): DimensionInterface
    {
        if ($dimension instanceof Closure) {
            $builder = new DimensionBuilder();
            call_user_func($dimension, $builder);
            $dimensions = $builder->getDimensions();

            if (count($dimensions) != 1) {
                throw new InvalidArgumentException('Your dimension builder should select 1 dimension');
            }

            return $dimensions[0];
        }

        return new Dimension($dimension);
    }

    /**
     * Normalize the given intervals into Interval objects.
     *
     * @param array $intervals
     *
     * @return array
     */
    protected function normalizeIntervals(array $intervals): array
    {
        $first = reset($intervals);

        // If first is an array or already a druid interval string or object we do not wrap it in an array
        if (!is_array($first) && !$this->isDruidInterval($first)) {
            $intervals = [$intervals];
        }

        return array_map(function ($interval) {

            if ($interval instanceof IntervalInterface) {
                return $interval;
            }

            // If it is a string we explode it into to elements
            if (is_string($interval)) {
                $interval = explode('/', $interval);
            }

            // If the value is an array and is not empty and has either one or 2 values its an interval array
            if (is_array($interval) && !empty(array_filter($interval)) && count($interval) < 3) {
                /** @scrutinizer ignore-type */
                return new Interval(...$interval);
            }

            throw new InvalidArgumentException(
                'Invalid type given in the interval array. We cannot process ' .
                var_export($interval, true)
            );
        }, $intervals);
    }

    /**
     * Returns true if the argument provided is a druid interval string or interface
     *
     * @param string|IntervalInterface $interval
     *
     * @return bool
     */
    protected function isDruidInterval($interval)
    {
        if ($interval instanceof IntervalInterface) {
            return true;
        }

        return is_string($interval) && strpos($interval, '/') !== false;
    }

    /**
     * Helper method to add an OR filter
     *
     * @param FilterInterface $filter
     */
    protected function addOrFilter(FilterInterface $filter): void
    {
        if (!$this->filter instanceof FilterInterface) {
            $this->filter = $filter;

            return;
        }

        if ($this->filter instanceof OrFilter) {
            $this->filter->addFilter($filter);

            return;
        }

        $this->filter = new OrFilter([$this->filter, $filter]);
    }

    /**
     * Helper method to add an AND filter
     *
     * @param FilterInterface $filter
     */
    protected function addAndFilter(FilterInterface $filter): void
    {
        if (!$this->filter instanceof FilterInterface) {
            $this->filter = $filter;

            return;
        }

        if ($this->filter instanceof AndFilter) {
            $this->filter->addFilter($filter);

            return;
        }

        $this->filter = new AndFilter([$this->filter, $filter]);
    }

    /**
     * @return \Level23\Druid\Filters\FilterInterface|null
     */
    public function getFilter(): ?FilterInterface
    {
        return $this->filter;
    }

    /**
     * @param \Closure|null $extraction
     *
     * @return \Level23\Druid\Extractions\ExtractionInterface|null
     */
    private function getExtraction(?Closure $extraction): ?ExtractionInterface
    {
        if (empty($extraction)) {
            return null;
        }

        $builder = new ExtractionBuilder();
        call_user_func($extraction, $builder);

        return $builder->getExtraction();
    }
}