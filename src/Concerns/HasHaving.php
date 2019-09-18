<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Closure;
use InvalidArgumentException;
use Level23\Druid\Filters\LikeFilter;
use Level23\Druid\Filters\FilterInterface;
use Level23\Druid\HavingFilters\HavingBuilder;
use Level23\Druid\HavingFilters\OrHavingFilter;
use Level23\Druid\HavingFilters\NotHavingFilter;
use Level23\Druid\HavingFilters\AndHavingFilter;
use Level23\Druid\HavingFilters\QueryHavingFilter;
use Level23\Druid\HavingFilters\EqualToHavingFilter;
use Level23\Druid\HavingFilters\LessThanHavingFilter;
use Level23\Druid\HavingFilters\HavingFilterInterface;
use Level23\Druid\HavingFilters\GreaterThanHavingFilter;
use Level23\Druid\HavingFilters\DimensionSelectorHavingFilter;

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

        if ($value === null && $operator !== null) {
            $value    = $operator;
            $operator = '=';
        }

        if (is_string($havingOrMetricOrClosure) && is_string($operator) && $value !== null) {
            if ($operator == '=') {
                $having = new DimensionSelectorHavingFilter($havingOrMetricOrClosure, (string)$value);
            } elseif ($operator == '<>' || $operator == '!=') {
                $having = new NotHavingFilter(new DimensionSelectorHavingFilter($havingOrMetricOrClosure,
                    (string)$value));
            } elseif ($operator == '>') {
                $having = new GreaterThanHavingFilter($havingOrMetricOrClosure, floatval($value));
            } elseif ($operator == '<') {
                $having = new LessThanHavingFilter($havingOrMetricOrClosure, floatval($value));
            } elseif ($operator == '>=') {
                $having = new OrHavingFilter([
                    new GreaterThanHavingFilter($havingOrMetricOrClosure, floatval($value)),
                    new EqualToHavingFilter($havingOrMetricOrClosure, floatval($value)),
                ]);
            } elseif ($operator == '<=') {
                $having = new OrHavingFilter([
                    new LessThanHavingFilter($havingOrMetricOrClosure, floatval($value)),
                    new EqualToHavingFilter($havingOrMetricOrClosure, floatval($value)),
                ]);
            } elseif (strtolower($operator) == 'like') {
                $having = new QueryHavingFilter(new LikeFilter($havingOrMetricOrClosure, $value));
            }
        } elseif ($havingOrMetricOrClosure instanceof FilterInterface) {
            $having = new QueryHavingFilter($havingOrMetricOrClosure);
        } elseif ($havingOrMetricOrClosure instanceof HavingFilterInterface) {
            $having = $havingOrMetricOrClosure;
        } elseif ($havingOrMetricOrClosure instanceof Closure) {

            // lets create a bew builder object where the user can mess around with
            $obj = new HavingBuilder();

            // call the user function
            call_user_func($havingOrMetricOrClosure, $obj);

            // Now retrieve the having filter which was created and add it to our current filter set.
            /**
             * @var HavingFilterInterface $filter
             */
            $having = $obj->getHaving();
        }

        if ($having === null) {
            throw new InvalidArgumentException('The arguments which you have supplied cannot be parsed');
        }

        strtolower($boolean) == 'and' ?
            $this->addAndHaving($having) :
            $this->addOrHaving($having);

        return $this;
    }

    /**
     * Add a having filter
     *
     * @param string|HavingFilterInterface|Closure $havingOrMetricOrClosure
     * @param string|null                          $operator
     * @param string|null                          $value
     *
     * @return $this
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
     * Helper method to add an OR filter
     *
     * @param \Level23\Druid\HavingFilters\HavingFilterInterface $havingFilter
     */
    protected function addOrHaving(HavingFilterInterface $havingFilter): void
    {
        if (!$this->having instanceof HavingFilterInterface) {
            $this->having = $havingFilter;

            return;
        }

        if ($this->having instanceof OrHavingFilter) {
            $this->having->addHavingFilter($havingFilter);

            return;
        }

        $this->having = new OrHavingFilter([$this->having, $havingFilter]);
    }

    /**
     * Helper method to add an OR filter
     *
     * @param \Level23\Druid\HavingFilters\HavingFilterInterface $havingFilter
     */
    protected function addAndHaving(HavingFilterInterface $havingFilter): void
    {
        if (!$this->having instanceof HavingFilterInterface) {
            $this->having = $havingFilter;

            return;
        }

        if ($this->having instanceof AndHavingFilter) {
            $this->having->addHavingFilter($havingFilter);

            return;
        }

        $this->having = new AndHavingFilter([$this->having, $havingFilter]);
    }
}