<?php
declare(strict_types=1);

namespace Level23\Druid\HavingFilters;

class GreaterThanHavingFilter implements HavingFilterInterface
{
    /**
     * @var string
     */
    protected $metric;

    /**
     * @var float|int
     */
    protected $value;

    /**
     * GreaterThanHaving constructor.
     *
     * @param string    $metric
     * @param int|float $value
     */
    public function __construct(string $metric, $value)
    {
        $this->metric = $metric;
        $this->value  = $value;
    }

    /**
     * Return the having filter as it can be used in a druid query.
     *
     * @return array
     */
    public function getHavingFilter(): array
    {
        return [
            'type'        => 'greaterThan',
            'aggregation' => $this->metric,
            'value'       => $this->value,
        ];
    }
}