<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\Extractions\ExtractionInterface;

/**
 * Class NullFilter
 *
 * @see     https://druid.apache.org/docs/latest/querying/filters#null-filter
 * @package Level23\Druid\Filters
 */
class NullFilter implements FilterInterface
{
    protected string $column;

    protected ?ExtractionInterface $extractionFunction;

    /**
     * NullFilter constructor.
     *
     * @param string $column Input column or virtual column name to filter.
     */
    public function __construct(string $column)
    {
        $this->column = $column;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|array<string,string|int|bool|array<mixed>>>
     */
    public function toArray(): array
    {
        $result = [
            'type'   => 'null',
            'column' => $this->column,
        ];

        return $result;
    }
}