<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Closure;
use Level23\Druid\FilterQueryBuilder;
use Level23\Druid\Filters\AndFilter;
use Level23\Druid\Filters\BoundFilter;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Filters\InFilter;
use Level23\Druid\Filters\JavascriptFilter;
use Level23\Druid\Filters\LikeFilter;
use Level23\Druid\Filters\LogicalExpressionFilterInterface;
use Level23\Druid\Filters\NotFilter;
use Level23\Druid\Filters\OrFilter;
use Level23\Druid\Filters\RegexFilter;
use Level23\Druid\Filters\SearchFilter;
use Level23\Druid\Filters\SelectorFilter;

trait HasFilter
{
    /**
     * @var \Level23\Druid\Filters\FilterInterface|null
     */
    protected $filter;

    /**
     * Filter our results where the given dimension matches the value based on the operator.
     * The operator can be '=', '>', '>=', '<', '<=', '<>', '!=' or 'like', 'regex', 'javascript', 'in'
     *
     * @param string|\Level23\Druid\Filters\FilterInterface|\Closure $filterOrDimensionOrClosure
     * @param string|null                                            $operator
     * @param mixed                                                  $value
     * @param string                                                 $boolean
     *
     * @return $this
     */
    public function where(
        $filterOrDimensionOrClosure,
        $operator = null,
        $value = null,
        $boolean = 'and'
    ) {
        $filter = null;
        if (is_string($filterOrDimensionOrClosure)) {
            if ($value === null && !empty($operator)) {
                $value    = $operator;
                $operator = '=';
            }

            $operator = strtolower((string)$operator);

            if ($operator == '=') {
                $filter = new SelectorFilter($filterOrDimensionOrClosure, $value);
            } elseif ($operator == '<>' || $operator == '!=') {
                $filter = new NotFilter(new SelectorFilter($filterOrDimensionOrClosure, $value));
            } elseif (in_array($operator, ['>', '>=', '<', '<='])) {
                $filter = new BoundFilter($filterOrDimensionOrClosure, $operator, (string)$value);
            } elseif ($operator == 'like') {
                $filter = new LikeFilter($filterOrDimensionOrClosure, $value);
            } elseif ($operator == 'javascript') {
                $filter = new JavascriptFilter($filterOrDimensionOrClosure, $value);
            } elseif ($operator == 'regex' || $operator == 'regexp') {
                $filter = new RegexFilter($filterOrDimensionOrClosure, $value);
            } elseif ($operator == 'search') {
                $filter = new SearchFilter($filterOrDimensionOrClosure, $value);
            } elseif ($operator == 'in') {
                $filter = new InFilter($filterOrDimensionOrClosure, $value);
            }
        } elseif ($filterOrDimensionOrClosure instanceof FilterInterface) {
            $filter = $filterOrDimensionOrClosure;
        } elseif ($filterOrDimensionOrClosure instanceof Closure) {

            // lets create a bew builder object where the user can mess around with
            $obj = new FilterQueryBuilder();

            // call the user function
            call_user_func($filterOrDimensionOrClosure, $obj);

            // Now retrieve the filter which was created and add it to our current filter set.
            $filter = $obj->getFilter();
        }

        if ($filter === null) {
            return $this;
        }

        if ($this->filter === null) {
            $this->filter = $filter;

            return $this;
        }

        $this->addFilter(
            $filter,
            strtolower($boolean) == 'and' ? AndFilter::class : OrFilter::class
        );

        return $this;
    }

    /**
     * @param string|FilterInterface $filterOrDimension
     * @param string|null            $operator
     * @param mixed|null             $value
     *
     * @return $this
     */
    public function orWhere($filterOrDimension, $operator = null, $value = null)
    {
        return $this->where($filterOrDimension, $operator, $value, 'or');
    }

    /**
     * Filter records where the given dimension exists in the given list of items
     *
     * @param string $dimension
     * @param array  $items
     *
     * @return $this
     */
    public function whereIn(string $dimension, array $items)
    {
        $filter = new InFilter($dimension, $items);

        return $this->where($filter);
    }

    /**
     * Filter records where the given dimension NOT exists in the given list of items
     *
     * @param string $dimension
     * @param array  $items
     *
     * @return $this
     */
    public function whereNotIn(string $dimension, array $items)
    {
        $filter = new NotFilter(new InFilter($dimension, $items));

        return $this->where($filter);
    }

    /**
     * Helper method to add a filter
     *
     * @param FilterInterface $filter
     * @param string          $type
     */
    protected function addFilter($filter, string $type)
    {
        if ($this->filter instanceof LogicalExpressionFilterInterface && $this->filter instanceof $type) {
            $this->filter->addFilter($filter);
        } else {
            $filters = [$this->filter, $filter];

            $this->filter = new $type($filters);
        }
    }

    /**
     * @return \Level23\Druid\Filters\FilterInterface|null
     */
    public function getFilter()
    {
        return $this->filter;
    }
}