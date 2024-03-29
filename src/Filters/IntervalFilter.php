<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\Interval\IntervalInterface;

/**
 * Class IntervalFilter
 *
 * The Interval filter enables range filtering on columns that contain long millisecond values,
 * with the boundaries specified as ISO 8601 time intervals. It is suitable for the __time column,
 * long metric columns, and dimensions with values that can be parsed as long milliseconds.
 *
 * This filter converts the ISO 8601 intervals to long millisecond start/end ranges and translates
 * to an OR of Bound filters on those millisecond ranges, with numeric comparison.
 * The Bound filters will have left-closed and right-open matching (i.e., start <= time < end).
 *
 * @package Level23\Druid\Filters
 */
class IntervalFilter implements FilterInterface
{
    protected string $dimension;

    /**
     * @var array|\Level23\Druid\Interval\IntervalInterface[]
     */
    protected array $intervals;

    /**
     * IntervalFilter constructor.
     *
     * @param string                    $dimension                 The dimension to filter on
     * @param array|IntervalInterface[] $intervals                 An array containing Interval objects. This
     *                                                             defines the time ranges to filter on.
     */
    public function __construct(
        string $dimension,
        array $intervals
    ) {
        $this->intervals = $intervals;
        $this->dimension = $dimension;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|array<string>|array<string,string|int|bool|array<mixed>>>
     */
    public function toArray(): array
    {
        $intervals = [];
        foreach ($this->intervals as $interval) {
            $intervals[] = $interval->getInterval();
        }

        return [
            'type'      => 'interval',
            'dimension' => $this->dimension,
            'intervals' => $intervals,
        ];
    }
}