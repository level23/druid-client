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
use Level23\Druid\Filters\FilterBuilder;
use Level23\Druid\Filters\BetweenFilter;
use Level23\Druid\Filters\IntervalFilter;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Filters\JavascriptFilter;
use Level23\Druid\Interval\IntervalInterface;
use Level23\Druid\Extractions\ExtractionBuilder;
use Level23\Druid\Extractions\ExtractionInterface;
use Level23\Druid\Filters\LogicalExpressionFilterInterface;

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
     * This filter will select records where the given dimension is greater than or equal to the given minValue, and
     * less than or equal to the given $maxValue.
     *
     * So in SQL syntax, this would be:
     * ```
     * WHERE dimension => $minValue AND dimension <= $maxValue
     * ```
     *
     * @param string        $dimension
     * @param string|int    $minValue
     * @param string|int    $maxValue
     * @param \Closure|null $extraction
     *
     * @return $this
     */
    public function whereBetween(string $dimension, $minValue, $maxValue, Closure $extraction = null)
    {
        $filter = new BetweenFilter($dimension, $minValue, $maxValue, null, $this->getExtraction($extraction));

        return $this->where($filter);
    }

    /**
     * This filter will select records where the given dimension is NOT between the given min and max value.
     *
     * So in SQL syntax, this would be:
     * ```
     * WHERE dimension < $minValue AND dimension > $maxValue
     * ```
     *
     * @param string        $dimension
     * @param string|int    $minValue
     * @param string|int    $maxValue
     * @param \Closure|null $extraction
     *
     * @return $this
     */
    public function whereNotBetween(string $dimension, $minValue, $maxValue, Closure $extraction = null)
    {
        $filter = new BetweenFilter($dimension, $minValue, $maxValue, null, $this->getExtraction($extraction));

        return $this->where(new NotFilter($filter));
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
     * Apply a where filter using a interval.
     *
     * @param string                    $dimension
     * @param \DateTime|string|int      $start DateTime object, unix timestamp or string accepted by
     *                                         DateTime::__construct or a raw interval string as required by druid.
     * @param \DateTime|string|int|null $stop  DateTime object, unix timestamp or string accepted by
     *                                         DateTime::__construct or null when $start contains an raw interval
     *                                         string.
     * @param \Closure|null             $extraction
     *
     * @return $this
     * @throws \Exception
     */
    public function whereInterval(string $dimension, $start, $stop = null, Closure $extraction = null)
    {
        $filter = new IntervalFilter(
            $dimension,
            [new Interval($start, $stop)],
            $this->getExtraction($extraction)
        );

        return $this->where($filter);
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
     * @param string        $dimension
     * @param array         $intervals
     * @param \Closure|null $extraction
     *
     * @return $this
     */
    public function whereInIntervals(string $dimension, array $intervals, Closure $extraction = null)
    {
        $intervals = array_map(function ($interval) {

            if ($interval instanceof IntervalInterface) {
                return $interval;
            }

            if (is_string($interval) && preg_match(
                    '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z\/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/',
                    $interval
                )) {
                return new Interval($interval);
            }

            if (is_array($interval) && count($interval) == 2) {
                list($start, $stop) = $interval;

                return new Interval($start, $stop);
            }

            throw new InvalidArgumentException(
                'Invalid type given in the interval array. We cannot process ' .
                var_export($interval, true)
            );
        }, $intervals);

        $filter = new IntervalFilter(
            $dimension,
            $intervals,
            $this->getExtraction($extraction)
        );

        return $this->where($filter);
    }

    /**
     * Helper method to add a filter
     *
     * @param FilterInterface $filter
     * @param string          $type
     */
    protected function addFilter(FilterInterface $filter, string $type)
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