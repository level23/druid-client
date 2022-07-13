<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\Extractions\ExtractionInterface;

/**
 * Class SearchFilter
 *
 * Search filters can be used to filter on partial string matches.
 *
 * @package Level23\Druid\Filters
 */
class SearchFilter implements FilterInterface
{
    protected string $dimension;

    protected ?ExtractionInterface $extractionFunction;

    /**
     * @var string|string[]
     */
    protected $value;

    protected bool $caseSensitive;

    /**
     * SearchFilter constructor.
     *
     * When an array of values is given, we expect the dimension value contains all
     * the values specified in this search query spec.
     *
     * @param string                   $dimension
     * @param string|string[]          $valueOrValues
     * @param bool                     $caseSensitive
     * @param ExtractionInterface|null $extractionFunction
     */
    public function __construct(
        string $dimension,
        $valueOrValues,
        bool $caseSensitive = false,
        ?ExtractionInterface $extractionFunction = null
    ) {
        $this->dimension          = $dimension;
        $this->extractionFunction = $extractionFunction;
        $this->value              = $valueOrValues;
        $this->caseSensitive      = $caseSensitive;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|array<string,string|int|bool|array<mixed>>>
     */
    public function toArray(): array
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
            $result['extractionFn'] = $this->extractionFunction->toArray();
        }

        return $result;
    }
}