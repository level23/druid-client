<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Closure;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\Filters\LikeFilter;
use Level23\Druid\Filters\LogicalExpressionHavingFilterInterface;
use Level23\Druid\HavingFilters\AndHavingFilter;
use Level23\Druid\HavingFilters\DimensionSelectorHavingFilter;
use Level23\Druid\HavingFilters\EqualToHavingFilter;
use Level23\Druid\HavingFilters\GreaterThanHavingFilter;
use Level23\Druid\HavingFilters\HavingFilterInterface;
use Level23\Druid\HavingFilters\LessThanHavingFilter;
use Level23\Druid\HavingFilters\NotHavingFilter;
use Level23\Druid\HavingFilters\OrHavingFilter;
use Level23\Druid\HavingFilters\QueryHavingFilter;
use Level23\Druid\HavingQueryBuilder;

trait HasHaving
{
    /**
     * @var HavingFilterInterface|null
     */
    protected $having;

    /**
     * Build our "having" part of the query.
     *
     * The operator can be '=', '>', '>=', '<', '<=', '<>', '!=' or 'like'
     *
     * @param string|HavingFilterInterface|Closure $havingOrMetricOrClosure
     * @param string|null                          $operator
     * @param string|null                          $value
     * @param string                               $boolean
     *
     * @return $this
     */
    public function having(
        $havingOrMetricOrClosure,
        $operator = null,
        $value = null,
        $boolean = 'and'
    ) {
        $having = null;

        if ($value === null && !empty($operator)) {
            $value    = $operator;
            $operator = '=';
        }

        if (is_string($havingOrMetricOrClosure) && is_string($operator) && $value !== null) {
            if ($operator == '=') {
                $having = new DimensionSelectorHavingFilter($havingOrMetricOrClosure, (string)$value);
            } elseif ($operator == '<>' || $operator == '!=') {
                $having = new NotHavingFilter(new EqualToHavingFilter($havingOrMetricOrClosure, floatval($value)));
            } elseif ($operator == '>') {
                $having = new GreaterThanHavingFilter($havingOrMetricOrClosure, floatval($value));
            } elseif ($operator == '<') {
                $having = new LessThanHavingFilter($havingOrMetricOrClosure, floatval($value));
            } elseif ($operator == '>=') {
                $having = new OrHavingFilter(
                    new GreaterThanHavingFilter($havingOrMetricOrClosure, floatval($value)),
                    new EqualToHavingFilter($havingOrMetricOrClosure, floatval($value))
                );
            } elseif ($operator == '<=') {
                $having = new OrHavingFilter(
                    new LessThanHavingFilter($havingOrMetricOrClosure, floatval($value)),
                    new EqualToHavingFilter($havingOrMetricOrClosure, floatval($value))
                );
            } elseif (strtolower($operator) == 'like') {
                $having = new QueryHavingFilter(new LikeFilter($havingOrMetricOrClosure, $value));
            }
        } elseif ($havingOrMetricOrClosure instanceof FilterInterface) {
            $having = new QueryHavingFilter($havingOrMetricOrClosure);
        } elseif ($havingOrMetricOrClosure instanceof HavingFilterInterface) {
            $having = $havingOrMetricOrClosure;
        } elseif ($havingOrMetricOrClosure instanceof Closure) {

            // lets create a bew builder object where the user can mess around with
            $obj = new HavingQueryBuilder();

            // call the user function
            call_user_func($havingOrMetricOrClosure, $obj);

            // Now retrieve the having filter which was created and add it to our current filter set.
            /**
             * @var HavingFilterInterface $filter
             */
            $having = $obj->getHaving();
        }

        if ($having === null) {
            return $this;
        }

        if ($this->having === null) {
            $this->having = $having;

            return $this;
        }

        $this->addHaving(
            $having,
            $boolean == 'and' ? AndHavingFilter::class : OrHavingFilter::class
        );

        return $this;
    }

    /**
     * Add a having filter
     *
     * @param string|HavingFilterInterface|Closure $havingOrMetricOrClosure
     * @param string|null                          $operator
     * @param string|null                          $value
     *
     * @return \Level23\Druid\Concerns\HasHaving
     */
    public function orHaving($havingOrMetricOrClosure, $operator = null, $value = null)
    {
        return $this->having($havingOrMetricOrClosure, $operator, $value, 'or');
    }

    /**
     * @return \Level23\Druid\HavingFilters\HavingFilterInterface|null
     */
    public function getHaving(): ?HavingFilterInterface
    {
        return $this->having;
    }

    /**
     * Helper method to add a filter
     *
     * @param \Level23\Druid\HavingFilters\HavingFilterInterface $havingFilter
     * @param string                                             $type
     */
    protected function addHaving(HavingFilterInterface $havingFilter, string $type)
    {
        if ($this->having instanceof LogicalExpressionHavingFilterInterface && $this->having instanceof $type) {
            $this->having->addHavingFilter($havingFilter);
        } else {
            $filters = [$this->having, $havingFilter];

            $this->having = new $type($filters);
        }
    }
}