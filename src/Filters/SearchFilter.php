<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\ExtractionFunctions\ExtractionFunctionInterface;

/**
 * Class SearchFilter
 *
 * Search filters can be used to filter on partial string matches.
 *
 * @package Level23\Druid\Filters
 */
class SearchFilter implements FilterInterface
{
    /**
     * @var string
     */
    protected $dimension;

    /**
     * @var \Level23\Druid\ExtractionFunctions\ExtractionFunctionInterface|null
     */
    protected $extractionFunction;

    /**
     * @var string|array
     */
    protected $value;

    /**
     * @var bool
     */
    protected $caseSensitive;

    /**
     * SearchFilter constructor.
     *
     * When an array of values are given, we expect the dimension value contains all
     * of the values specified in this search query spec.
     *
     * @param string                           $dimension
     * @param string|string[]|array            $valueOrValues
     * @param bool                             $caseSensitive
     * @param ExtractionFunctionInterface|null $extractionFunction
     */
    public function __construct(
        string $dimension,
        $valueOrValues,
        bool $caseSensitive = false,
        ExtractionFunctionInterface $extractionFunction = null
    ) {
        $this->dimension          = $dimension;
        $this->extractionFunction = $extractionFunction;
        $this->value              = $valueOrValues;
        $this->caseSensitive      = $caseSensitive;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array
     */
    public function getFilter(): array
    {
        if (is_array($this->value)) {
            $query = [
                'type'          => 'fragment',
                'values'        => $this->value,
                'caseSensitive' => $this->caseSensitive,
            ];
        } else {
            $query = [
                'type'          => 'contains',
                'value'         => $this->value,
                'caseSensitive' => $this->caseSensitive,
            ];
        }

        $result = [
            'type'      => 'search',
            'dimension' => $this->dimension,
            'query'     => $query,
        ];

        if ($this->extractionFunction) {
            $result['extractionFn'] = $this->extractionFunction->getExtractionFunction();
        }

        return $result;
    }
}