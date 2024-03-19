<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Closure;
use InvalidArgumentException;
use Level23\Druid\Types\DataType;
use Level23\Druid\Filters\InFilter;
use Level23\Druid\Filters\OrFilter;
use Level23\Druid\Filters\AndFilter;
use Level23\Druid\Filters\NotFilter;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Filters\LikeFilter;
use Level23\Druid\Filters\NullFilter;
use Level23\Druid\Filters\RegexFilter;
use Level23\Druid\Filters\RangeFilter;
use Level23\Druid\Filters\SearchFilter;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\Filters\FilterBuilder;
use Level23\Druid\Filters\BetweenFilter;
use Level23\Druid\Filters\IntervalFilter;
use Level23\Druid\Filters\EqualityFilter;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Filters\JavascriptFilter;
use Level23\Druid\Filters\ExpressionFilter;
use Level23\Druid\Interval\IntervalInterface;
use Level23\Druid\Dimensions\DimensionBuilder;
use Level23\Druid\Filters\SpatialRadiusFilter;
use Level23\Druid\Filters\ArrayContainsFilter;
use Level23\Druid\Filters\SpatialPolygonFilter;
use Level23\Druid\Dimensions\DimensionInterface;
use Level23\Druid\Filters\ColumnComparisonFilter;
use Level23\Druid\Filters\SpatialRectangularFilter;

trait HasFilter
{
    protected ?QueryBuilder $query = null;

    protected ?FilterInterface $filter = null;

    /**
     * Filter our results where the given dimension matches the value based on the operator.
     * The operator can be '=', '>', '>=', '<', '<=', '<>', '!=', 'like', 'not like', 'regex', 'not regex',
     * 'javascript', 'not javascript', 'search' and 'not search'
     *
     * @param \Closure|string|FilterInterface     $filterOrDimensionOrClosure The dimension which you want to filter.
     * @param int|string|float|bool|null          $operator                   The operator which you want to use to
     *                                                                        filter. See below for a complete list of
     *                                                                        supported operators.
     * @param int|string|string[]|null|float|bool $value                      The value which you want to use in your
     *                                                                        filter comparison
     * @param string                              $boolean                    This influences how this filter will be
     *                                                                        joined with previous added filters.
     *                                                                        Should
     *                                                                        both filters apply ("and") or one or the
     *                                                                        other ("or") ? Default is "and".
     *
     * @return $this
     */
    public function where(
        Closure|string|FilterInterface $filterOrDimensionOrClosure,
        int|string|float|bool|null $operator = null,
        array|int|string|float|bool $value = null,
        string $boolean = 'and'
    ): self {

        if ($filterOrDimensionOrClosure instanceof FilterInterface) {
            return $this->useFilter($filterOrDimensionOrClosure, $boolean);
        }

        if ($filterOrDimensionOrClosure instanceof Closure) {
            return $this->useClosureAsFilter($filterOrDimensionOrClosure, $boolean);
        }

        if ($operator === null && $value !== null) {
            throw new InvalidArgumentException('You have to supply an operator when you supply a dimension as string');
        }

        // Allow shorthand method where the operator is left out, just like laravel does.
        if ($value === null && $operator !== null && !in_array($operator, ['=', '!=', '<>'])) {
            $value    = $operator;
            $operator = '=';
        }

        if ($operator === null || $value === null) {
            $operator = '=';
        }

        if (is_bool($value)) {
            $value = $value ? 1 : 0;
        }

        $operator = strtolower((string)$operator);

        if ($operator == 'search') {
            if (is_int($value) || is_float($value)) {
                $value = (string)$value;
            }

            return $this->useFilter(new SearchFilter(
                $filterOrDimensionOrClosure, $value ?? '', false
            ), $boolean);
        }

        if ($operator == 'not search') {
            if (is_int($value) || is_float($value)) {
                $value = (string)$value;
            }

            return $this->useFilter(new NotFilter(new SearchFilter(
                $filterOrDimensionOrClosure, $value ?? '', false
            )), $boolean);
        }

        // -- Anything below does not accept an array as value --

        if (is_array($value)) {
            throw new InvalidArgumentException('Given $value is invalid in combination with operator ' . $operator);
        }

        /** @var int|string|float|null $value */

        if ($operator == '=') {
            return $this->useFilter(new EqualityFilter(
                $filterOrDimensionOrClosure,
                is_null($value) ? '' : $value,
                null # Auto-detect type...
            ), $boolean);
        }

        if ($operator == '<>' || $operator == '!=') {
            $filter = new EqualityFilter(
                $filterOrDimensionOrClosure,
                is_null($value) ? '' : $value,
                null # Auto-detect type...
            );

            return $this->useFilter(new NotFilter($filter), $boolean);
        }

        if (in_array($operator, ['>', '>=', '<', '<='])) {
            return $this->useFilter(new RangeFilter(
                $filterOrDimensionOrClosure,
                $operator,
                $value ?? '',
                null
            ), $boolean);
        }

        if ($operator == 'like') {
            return $this->useFilter(new LikeFilter(
                $filterOrDimensionOrClosure, (string)$value, '\\'
            ), $boolean);
        }

        if ($operator == 'not like') {
            return $this->useFilter(new NotFilter(
                new LikeFilter($filterOrDimensionOrClosure, (string)$value, '\\')
            ), $boolean);
        }

        if ($operator == 'javascript') {
            return $this->useFilter(new JavascriptFilter(
                $filterOrDimensionOrClosure,
                (string)$value
            ), $boolean);
        }

        if ($operator == 'not javascript') {
            return $this->useFilter(new NotFilter(
                new JavascriptFilter($filterOrDimensionOrClosure, (string)$value)
            ), $boolean);
        }

        if ($operator == 'regex' || $operator == 'regexp') {
            return $this->useFilter(new RegexFilter(
                $filterOrDimensionOrClosure,
                (string)$value,
            ), $boolean);
        }

        if ($operator == 'not regex' || $operator == 'not regexp') {
            return $this->useFilter(new NotFilter(
                new RegexFilter($filterOrDimensionOrClosure, (string)$value)
            ), $boolean);
        }

        throw new InvalidArgumentException('The arguments which you have supplied cannot be parsed.');
    }

    /**
     * Add a filter to our known filters in the given boolean mode (and / or)
     *
     * @param \Level23\Druid\Filters\FilterInterface $filter
     * @param string                                 $boolean
     *
     * @return $this
     */
    private function useFilter(FilterInterface $filter, string $boolean): self
    {
        strtolower($boolean) == 'and' ? $this->addAndFilter($filter) : $this->addOrFilter($filter);

        return $this;
    }

    /**
     * Use a closure as a filter. The closure will receive a FilterBuilder as parameter.
     *
     * @param \Closure $closure
     * @param string   $boolean
     *
     * @return $this
     */
    private function useClosureAsFilter(Closure $closure, string $boolean): self
    {
        // let's create a new builder object where the user can mess around with
        $builder = new FilterBuilder($this->query);

        // call the user function
        call_user_func($closure, $builder);

        $filter = $builder->getFilter();

        if (!$filter) {
            throw new InvalidArgumentException('The arguments which you have supplied cannot be parsed.');
        }

        // Now retrieve the filter which was created and add it to our current filter set.
        return $this->useFilter($filter, $boolean);
    }

    /**
     * Check if an ARRAY contains a specific element but can also match against any type of column.
     * When matching against scalar columns, scalar columns are treated as single-element arrays.
     *
     * @param string                $column Input column or virtual column name to filter on.
     * @param int|float|string|null $value  Array element value to match. This value can be null.
     * @param string                $boolean
     *
     * @return $this
     * @see https://druid.apache.org/docs/latest/querying/filters/#array-contains-element-filter
     *
     */
    public function whereArrayContains(string $column, int|float|string|null $value, string $boolean = 'and'): self
    {
        $filter = new ArrayContainsFilter($column, $value);

        return $this->useFilter($filter, $boolean);
    }

    /**
     * Check if an ARRAY contains a specific element but can also match against any type of column.
     * When matching against scalar columns, scalar columns are treated as single-element arrays.
     *
     * @param string                $column Input column or virtual column name to filter on.
     * @param int|float|string|null $value  Array element value to match. This value can be null.
     *
     * @return $this
     */
    public function orWhereArrayContains(string $column, int|float|string|null $value): self
    {
        return $this->whereArrayContains($column, $value, 'or');
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
    public function whereNot(Closure $filterBuilder, string $boolean = 'and'): self
    {
        // let's create a bew builder object where the user can mess around with
        $builder = new FilterBuilder($this->query);

        // call the user function
        call_user_func($filterBuilder, $builder);

        // Now retrieve the filter which was created and add it to our current filter set.
        $filter = $builder->getFilter();
        if ($filter) {
            return $this->where(new NotFilter($filter), null, null, $boolean);
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
    public function orWhereNot(Closure $filterBuilder): self
    {
        return $this->whereNot($filterBuilder, 'or');
    }

    /**
     * This applies a filter, only it will join previous added filters with an "or" instead of an "and".
     * See the documentation of the "where" method for more information
     *
     * @param string|FilterInterface              $filterOrDimension
     * @param string|int|float|bool|null          $operator
     * @param int|string|float|bool|string[]|null $value
     *
     * @return $this
     * @see \Level23\Druid\Concerns\HasFilter::where()
     */
    public function orWhere(
        string|FilterInterface $filterOrDimension,
        string|int|float|bool $operator = null,
        array|int|float|string|bool $value = null
    ): self {
        return $this->where($filterOrDimension, $operator, $value, 'or');
    }

    /**
     * Filter records where the given dimension exists in the given list of items
     *
     * @param string         $dimension  The dimension which you want to filter
     * @param string[]|int[] $items      A list of values. We will return records where the dimension is in this list.
     * @param string         $boolean    This influences how this filter will be joined with previous added filters.
     *                                   Should both filters apply ("and") or one or the other ("or") ? Default is
     *                                   "and".
     *
     * @return $this
     */
    public function whereIn(string $dimension, array $items, string $boolean = 'and'): self
    {
        $filter = new InFilter($dimension, $items);

        return $this->where($filter, null, null, $boolean);
    }

    /**
     * Filter on (virtual) columns with a value which is equal to NULL. This is especially useful when
     * `druid.generic.useDefaultValueForNull=false` was configured.
     *
     * @param string $column
     * @param string $boolean
     *
     * @return $this
     */
    public function whereNull(string $column, string $boolean = 'and'): self
    {
        $filter = new NullFilter($column);

        strtolower($boolean) == 'and' ? $this->addAndFilter($filter) : $this->addOrFilter($filter);

        return $this;
    }

    /**
     * Filter on (virtual) columns with a value which is equal to NULL. This is especially useful when
     * `druid.generic.useDefaultValueForNull=false` was configured.
     *
     * @param string $column
     *
     * @return $this
     */
    public function orWhereNull(string $column): self
    {
        return $this->whereNull($column, 'or');
    }

    /**
     * The expression filter allows for the implementation of arbitrary conditions, leveraging the Druid expression
     * system.
     *
     * This filter allows for more flexibility, but it might be less performant than a combination of the other filters
     * on this page due to the fact that not all filter optimizations are in place yet.
     *
     * @param string $expression        The expression to filter on
     * @param string $boolean           This influences how this filter will be joined with previous added filters.
     *                                  Should both filters apply ("and") or one or the other ("or") ? Default is
     *                                  "and".
     *
     * @return $this
     * @see https://druid.apache.org/docs/latest/misc/math-expr.html
     */
    public function whereExpression(string $expression, string $boolean = 'and'): self
    {
        $filter = new ExpressionFilter($expression);

        strtolower($boolean) == 'and' ? $this->addAndFilter($filter) : $this->addOrFilter($filter);

        return $this;
    }

    /**
     * The expression filter allows for the implementation of arbitrary conditions, leveraging the Druid expression
     * system.
     *
     * This filter allows for more flexibility, but it might be less performant than a combination of the other filters
     * on this page due to the fact that not all filter optimizations are in place yet.
     *
     * @param string $expression
     *
     * @return $this
     */
    public function orWhereExpression(string $expression): self
    {
        return $this->whereExpression($expression, 'or');
    }

    /**
     * Filter records where the given dimension exists in the given list of items.
     *
     * If there are previously defined filters, this filter will be joined with an "or".
     *
     * @param string         $dimension The dimension which you want to filter
     * @param string[]|int[] $items     A list of values. We will return records where the dimension is in this list.
     *
     * @return $this
     */
    public function orWhereIn(string $dimension, array $items): self
    {
        return $this->whereIn($dimension, $items, 'or');
    }

    /**
     * Filter records where dimensionA is equal to dimensionB.
     *
     * @param Closure|string $dimensionA The dimension which you want to compare, or a Closure which will receive a
     *                                   DimensionBuilder which allows you to select a dimension in a more advance way.
     * @param Closure|string $dimensionB The dimension which you want to compare, or a Closure which will receive a
     *                                   DimensionBuilder which allows you to select a dimension in a more advance way.
     * @param string         $boolean    This influences how this filter will be joined with previous added filters.
     *                                   Should both filters apply ("and") or one or the other ("or") ? Default is
     *                                   "and".
     *
     * @return $this
     */
    public function whereColumn(Closure|string $dimensionA, Closure|string $dimensionB, string $boolean = 'and'): self
    {
        $filter = new ColumnComparisonFilter(
            $this->columnCompareDimension($dimensionA),
            $this->columnCompareDimension($dimensionB)
        );

        return $this->where($filter, null, null, $boolean);
    }

    /**
     * Filter records where dimensionA is equal to dimensionB.
     * You can either supply a string or a Closure. The Closure will receive a DimensionBuilder object, which allows
     * you to select a dimension.
     *
     * Example:
     * ```php
     * $builder->orWhereColumn('initials', function(DimensionBuilder $dimensionBuilder) {
     *   $dimensionBuilder->select('first_name');
     * });
     * ```
     *
     * @param Closure|string $dimensionA The dimension which you want to compare, or a Closure which will receive a
     *                                   DimensionBuilder which allows you to select a dimension in a more advance way.
     * @param Closure|string $dimensionB The dimension which you want to compare, or a Closure which will receive a
     *                                   DimensionBuilder which allows you to select a dimension in a more advance way.
     *
     * @return $this
     */
    public function orWhereColumn(Closure|string $dimensionA, Closure|string $dimensionB): self
    {
        return $this->whereColumn($dimensionA, $dimensionB, 'or');
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
     * @param string           $dimension The dimension which you want to filter
     * @param int|float|string $minValue  The minimum value where the dimension should match. It should be equal or
     *                                    greater than this value.
     * @param int|float|string $maxValue  The maximum value where the dimension should match. It should be less than
     *                                    this value.
     * @param DataType|null    $valueType String specifying the type of bounds to match. The valueType determines how
     *                                    Druid interprets the matchValue to assist in converting to the type of the
     *                                    matched column and also defines the type of comparison used when matching
     *                                    values.
     * @param string           $boolean   This influences how this filter will be joined with previous added filters.
     *                                    Should both filters apply ("and") or one or the other ("or") ? Default is
     *                                    "and".
     *
     * @return $this
     */
    public function whereBetween(
        string $dimension,
        int|float|string $minValue,
        int|float|string $maxValue,
        ?DataType $valueType = null,
        string $boolean = 'and'
    ): self {
        $filter = new BetweenFilter($dimension, $minValue, $maxValue, $valueType);

        return $this->where($filter, null, null, $boolean);
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
     * @param string           $dimension The dimension which you want to filter
     * @param int|float|string $minValue  The minimum value where the dimension should match. It should be equal or
     *                                    greater than this value.
     * @param int|float|string $maxValue  The maximum value where the dimension should match. It should be less than
     *                                    this value.
     * @param DataType|null    $valueType String specifying the type of bounds to match. The valueType determines how
     *                                    Druid interprets the matchValue to assist in converting to the type of the
     *                                    matched column and also defines the type of comparison used when matching
     *                                    values.
     *
     * @return $this
     */
    public function orWhereBetween(
        string $dimension,
        int|float|string $minValue,
        int|float|string $maxValue,
        ?DataType $valueType = null
    ): self {
        return $this->whereBetween($dimension, $minValue, $maxValue, $valueType, 'or');
    }

    /**
     * Filter on records which match using a bitwise AND comparison.
     *
     * Only records will match where the dimension contains ALL bits which are also enabled in the given $flags
     * argument. Support for 64-bit integers are supported.
     *
     * @param string $dimension     The dimension which contains int values where you want to do a bitwise AND check
     *                              against.
     * @param int    $flags         The bits which you want to check if they are enabled in the given dimension.
     *
     * @return $this
     */
    public function orWhereFlags(string $dimension, int $flags): self
    {
        return $this->whereFlags($dimension, $flags, 'or');
    }

    /**
     * Filter on records which match using a bitwise AND comparison.
     *
     * Only records will match where the dimension contains ALL bits which are also enabled in the given $flags
     * argument. Support for 64-bit integers are supported.
     *
     * @param string $dimension     The dimension which contains int values where you want to do a bitwise AND check
     *                              against.
     * @param int    $flags         The bits which you want to check if they are enabled in the given dimension.
     * @param string $boolean       This influences how this filter will be joined with previous added filters. Should
     *                              both filters apply ("and") or one or the other ("or") ? Default is "and".
     *
     * @return $this
     * @throws \BadFunctionCallException
     */
    public function whereFlags(
        string $dimension,
        int $flags,
        string $boolean = 'and'
    ): self {
        // If we do not have access to a query builder object, we cannot select our
        // flags value as a virtual column. This situation can happen for example when
        // we are in a task-builder. In that case, we will use the expression filter.
        // This has not our preference as it is slower.
        if (!$this->query instanceof QueryBuilder) {
            return $this->whereExpression('bitwiseAnd("' . $dimension . '", ' . $flags . ') == ' . $flags);
        }

        $placeholder                 = 'v' . count($this->query->placeholders);
        $this->query->placeholders[] = $placeholder;

        $this->query->virtualColumn(
            'bitwiseAnd("' . $dimension . '", ' . $flags . ')',
            $placeholder,
            DataType::LONG
        );

        return $this->where($placeholder, '=', $flags, $boolean);
    }

    /**
     * Filter on a dimension where the value exists in the given intervals array.
     *
     * The intervals array can contain the following:
     * - an Interval object
     * - a raw interval string as used in druid. For example: 2019-04-15T08:00:00.000Z/2019-04-15T09:00:00.000Z
     * - an array which contains 2 elements, a start and stop date. These can be an DateTime object, a unix timestamp
     *   or anything which can be parsed by DateTime::__construct
     *
     * @param string                                                               $dimension  The dimension which you
     *                                                                                         want to filter
     * @param array<string|IntervalInterface|array<string|\DateTimeInterface|int>> $intervals  The interval which you
     *                                                                                         want to match. See above
     *                                                                                         for more info.
     * @param string                                                               $boolean    This influences how this
     *                                                                                         filter will be joined
     *                                                                                         with previous added
     *                                                                                         filters. Should both
     *                                                                                         filters apply
     *                                                                                         ("and") or one or the
     *                                                                                         other ("or") ? Default
     *                                                                                         is
     *                                                                                         "and".
     *
     * @return $this
     * @throws \Exception
     */
    public function whereInterval(
        string $dimension,
        array $intervals,
        string $boolean = 'and'
    ): self {
        $filter = new IntervalFilter($dimension, $this->normalizeIntervals($intervals));

        return $this->where($filter, null, null, $boolean);
    }

    /**
     * Filter on a dimension where the value exists in the given intervals array.
     *
     * The intervals array can contain the following:
     * - an Interval object
     * - a raw interval string as used in druid. For example: 2019-04-15T08:00:00.000Z/2019-04-15T09:00:00.000Z
     * - an array which contains 2 elements, a start and stop date. These can be an DateTime object, a unix timestamp
     *   or anything which can be parsed by DateTime::__construct
     *
     * @param string                                                               $dimension  The dimension which you
     *                                                                                         want to filter
     * @param array<string|IntervalInterface|array<string|\DateTimeInterface|int>> $intervals  The interval which you
     *                                                                                         want to match. See above
     *                                                                                         for more info.
     *
     * @return $this
     * @throws \Exception
     */
    public function orWhereInterval(string $dimension, array $intervals): self
    {
        return $this->whereInterval($dimension, $intervals, 'or');
    }

    /**
     * Filter on a spatial dimension where the spatial dimension value (x,y coordinates) are between the
     * given min and max coordinates.
     *
     * @param string  $dimension The name of the spatial dimension.
     * @param float[] $minCoords List of minimum dimension coordinates for coordinates [x, y, z, …]
     * @param float[] $maxCoords List of maximum dimension coordinates for coordinates [x, y, z, …]
     * @param string  $boolean   This influences how this filter will be joined with previous added filters.
     *                           Should both filters apply ("and") or one or the other ("or") ? Default is
     *                           "and".
     *
     * @return $this
     */
    public function whereSpatialRectangular(
        string $dimension,
        array $minCoords,
        array $maxCoords,
        string $boolean = 'and'
    ): self {
        $filter = new SpatialRectangularFilter($dimension, $minCoords, $maxCoords);

        strtolower($boolean) == 'and' ? $this->addAndFilter($filter) : $this->addOrFilter($filter);

        return $this;
    }

    /**
     * Select all records where the spatial dimension is within the given radios.
     * You can specify an x,y, z coordinate and the radius. All the records where the spatial dimension
     * is within the given area will be returned.
     *
     * @param string  $dimension The name of the spatial dimension.
     * @param float[] $coords    Origin coordinates in the form [x, y, z, …]
     * @param float   $radius    The float radius value
     * @param string  $boolean   This influences how this filter will be joined with previous added filters.
     *                           Should both filters apply ("and") or one or the other ("or") ? Default is
     *                           "and".
     *
     * @return $this
     */
    public function whereSpatialRadius(string $dimension, array $coords, float $radius, string $boolean = 'and'): self
    {
        $filter = new SpatialRadiusFilter($dimension, $coords, $radius);

        strtolower($boolean) == 'and' ? $this->addAndFilter($filter) : $this->addOrFilter($filter);

        return $this;
    }

    /**
     * Return the records where the spatial dimension is within the area of the defined polygon.
     *
     * @param string  $dimension The name of the spatial dimension.
     * @param float[] $abscissa  (The x-axis) Horizontal coordinate for corners of the polygon
     * @param float[] $ordinate  (The y-axis) Vertical coordinate for corners of the polygon
     * @param string  $boolean   This influences how this filter will be joined with previous added filters.
     *                           Should both filters apply ("and") or one or the other ("or") ? Default is
     *                           "and".
     *
     * @return $this
     */
    public function whereSpatialPolygon(
        string $dimension,
        array $abscissa,
        array $ordinate,
        string $boolean = 'and'
    ): self {
        $filter = new SpatialPolygonFilter($dimension, $abscissa, $ordinate);

        strtolower($boolean) == 'and' ? $this->addAndFilter($filter) : $this->addOrFilter($filter);

        return $this;
    }

    /**
     * Filter on a spatial dimension where the spatial dimension value (x,y coordinates) are between the
     * given min and max coordinates.
     *
     * @param string  $dimension The name of the spatial dimension.
     * @param float[] $minCoords List of minimum dimension coordinates for coordinates [x, y, z, …]
     * @param float[] $maxCoords List of maximum dimension coordinates for coordinates [x, y, z, …]
     *
     * @return $this
     */
    public function orWhereSpatialRectangular(string $dimension, array $minCoords, array $maxCoords): self
    {
        return $this->whereSpatialRectangular($dimension, $minCoords, $maxCoords, 'or');
    }

    /**
     * Select all records where the spatial dimension is within the given radios.
     * You can specify an x,y, z coordinate and the radius. All the records where the spatial dimension
     * is within the given area will be returned.
     *
     * @param string  $dimension The name of the spatial dimension.
     * @param float[] $coords    Origin coordinates in the form [x, y, z, …]
     * @param float   $radius    The float radius value
     *
     * @return $this
     */
    public function orWhereSpatialRadius(string $dimension, array $coords, float $radius): self
    {
        return $this->whereSpatialRadius($dimension, $coords, $radius, 'or');
    }

    /**
     * Return the records where the spatial dimension is within the area of the defined polygon.
     *
     * @param string  $dimension The name of the spatial dimension.
     * @param float[] $abscissa  (The x-axis) Horizontal coordinate for corners of the polygon
     * @param float[] $ordinate  (The y-axis) Vertical coordinate for corners of the polygon
     *
     * @return $this
     */
    public function orWhereSpatialPolygon(string $dimension, array $abscissa, array $ordinate): self
    {
        return $this->whereSpatialPolygon($dimension, $abscissa, $ordinate, 'or');
    }

    /**
     * Normalize the given dimension to a DimensionInterface object.
     *
     * @param Closure|string $dimension
     *
     * @return \Level23\Druid\Dimensions\DimensionInterface
     * @throws InvalidArgumentException
     */
    protected function columnCompareDimension(Closure|string $dimension): DimensionInterface
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
     * @param array<string|IntervalInterface|array<string|\DateTimeInterface|int>> $intervals
     *
     * @return array<IntervalInterface>
     * @throws \Exception
     */
    protected function normalizeIntervals(array $intervals): array
    {
        if (sizeof($intervals) == 0) {
            return [];
        }

        $first = reset($intervals);

        // If first is an array or already a druid interval string or object we do not wrap it in an array
        if (!is_array($first) && !$this->isDruidInterval($first)) {
            $intervals = [$intervals];
        }

        return array_map(function ($interval) {

            /** @var string|IntervalInterface|array<string|\DateTimeInterface|int> $interval */
            if ($interval instanceof IntervalInterface) {
                return $interval;
            }

            // If it is a string we explode it into to elements
            if (is_string($interval)) {
                $interval = explode('/', $interval);
            }

            // If the value is an array and is not empty and has either one or 2 values it's an interval array
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
     * @param mixed $interval
     *
     * @return bool
     */
    protected function isDruidInterval(mixed $interval): bool
    {
        if ($interval instanceof IntervalInterface) {
            return true;
        }

        return is_string($interval) && str_contains($interval, '/');
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
}