<?php
declare(strict_types=1);

namespace Level23\Druid\Extractions;

/**
 * Class SearchQueryExtraction
 *
 * @package Level23\Druid\Extractions
 */
class SearchQueryExtraction implements ExtractionInterface
{
    /**
     * @var array|string
     */
    protected $valueOrValues;

    /**
     * @var bool
     */
    protected $caseSensitive;

    /**
     * SearchQueryExtraction constructor.
     *
     * @param string|array $valueOrValues
     * @param bool         $caseSensitive
     */
    public function __construct($valueOrValues, bool $caseSensitive = false)
    {

        $this->valueOrValues = $valueOrValues;
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * Return the Extraction Function so it can be used in a druid query.
     *
     * @return array
     */
    public function getExtractionFunction(): array
    {
        if (is_array($this->valueOrValues)) {
            $response = [
                'type'           => 'fragment',
                'case_sensitive' => $this->caseSensitive,
                'values'         => $this->valueOrValues,
            ];
        } else {
            $response = [
                'type'           => 'contains',
                'case_sensitive' => $this->caseSensitive,
                'value'          => $this->valueOrValues,
            ];
        }

        return $response;
    }
}