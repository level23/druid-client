<?php
declare(strict_types=1);

namespace Level23\Druid\HavingFilters;

class EqualToHavingFilter implements HavingFilterInterface
{
    protected string $metric;

    /**
     * @var float|int
     */
    protected $value;

    /**
     * EqualToHavingFilter constructor.
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
    public function toArray(): array
    {
        return [
            'type'        => 'equalTo',
            'aggregation' => $this->metric,
            'value'       => $this->value,
        ];
    }
}