<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\Extractions\ExtractionInterface;

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
    /**
     * @var string
     */
    protected $dimension;

    /**
     * @var array
     */
    protected $intervals;

    /**
     * @var \Level23\Druid\Extractions\ExtractionInterface|null
     */
    protected $extractionFunction;

    /**
     * IntervalFilter constructor.
     *
     * @param string                   $dimension                  The dimension to filter on
     * @param array                    $intervals                  A array containing ISO-8601 interval strings. This
     *                                                             defines the time ranges to filter on.
     * @param ExtractionInterface|null $extractionFunction         If an extraction function is used with this filter,
     *                                                             the extraction function should output values that
     *                                                             are parsable as long milliseconds.
     */
    public function __construct(
        string $dimension,
        array $intervals,
        ExtractionInterface $extractionFunction = null
    ) {
        $this->intervals          = $intervals;
        $this->dimension          = $dimension;
        $this->extractionFunction = $extractionFunction;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array
     */
    public function getFilter(): array
    {
        $result = [
            'type'      => 'interval',
            'dimension' => $this->dimension,
            'intervals' => $this->intervals,
        ];

        if ($this->extractionFunction) {
            $result['extractionFn'] = $this->extractionFunction->getExtractionFunction();
        }

        return $result;
    }
}