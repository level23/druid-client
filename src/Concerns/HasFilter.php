<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Closure;
use InvalidArgumentException;
use Level23\Druid\ExtractionBuilder;
use Level23\Druid\Extractions\ExtractionInterface;
use Level23\Druid\FilterBuilder;
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
    // use BuildsExtraction;

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
     * @param \Closure|null                                          $extraction
     * @param string                                                 $boolean
     *
     * @return $this
     */
    public function where(
        $filterOrDimensionOrClosure,
        $operator = null,
        $value = null,
        Closure $extraction = null,
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
            } elseif ($operator == 'javascript') {
                $filter = new JavascriptFilter($filterOrDimensionOrClosure, $value, $this->getExtraction($extraction));
            } elseif ($operator == 'regex' || $operator == 'regexp') {
                $filter = new RegexFilter($filterOrDimensionOrClosure, $value, $this->getExtraction($extraction));
            } elseif ($operator == 'search') {
                $filter = new SearchFilter(
                    $filterOrDimensionOrClosure, $value, false, $this->getExtraction($extraction)
                );
            } elseif ($operator == 'in') {
                $filter = new InFilter($filterOrDimensionOrClosure, $value, $this->getExtraction($extraction));
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
            throw new InvalidArgumentException(
                'The arguments which you have supplied cannot be parsed: ' . var_export(func_get_args(), true)
            );
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
     * @param \Closure|null          $extraction
     *
     * @return $this
     */
    public function orWhere($filterOrDimension, $operator = null, $value = null, Closure $extraction = null)
    {
        return $this->where($filterOrDimension, $operator, $value, $extraction, 'or');
    }

    /**
     * Filter records where the given dimension exists in the given list of items
     *
     * @param string        $dimension
     * @param array         $items
     * @param \Closure|null $extraction
     *
     * @return $this
     */
    public function whereIn(string $dimension, array $items, Closure $extraction = null)
    {

        $filter = new InFilter($dimension, $items, $this->getExtraction($extraction));

        return $this->where($filter);
    }

    /**
     * Filter records where the given dimension NOT exists in the given list of items
     *
     * @param string        $dimension
     * @param array         $items
     * @param \Closure|null $extraction
     *
     * @return $this
     */
    public function whereNotIn(string $dimension, array $items, Closure $extraction = null)
    {
        $filter = new NotFilter(new InFilter($dimension, $items, $this->getExtraction($extraction)));

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