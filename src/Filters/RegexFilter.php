<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\Extractions\ExtractionInterface;

/**
 * Class RegexFilter
 *
 * The regular expression filter is similar to the selector filter, but using regular expressions.
 * It matches the specified dimension with the given pattern. The pattern can be any standard Java regular expression.
 *
 * @see     http://docs.oracle.com/javase/6/docs/api/java/util/regex/Pattern.html
 * @package Level23\Druid\Filters
 */
class RegexFilter implements FilterInterface
{
    protected string $dimension;

    protected string $pattern;

    protected ?ExtractionInterface $extractionFunction;

    /**
     * RegexFilter constructor.
     *
     * @param string                   $dimension
     * @param string                   $pattern A Java regex pattern
     *
     * @param ExtractionInterface|null $extractionFunction
     *
     * @see http://docs.oracle.com/javase/6/docs/api/java/util/regex/Pattern.html
     */
    public function __construct(
        string $dimension,
        string $pattern,
        ExtractionInterface $extractionFunction = null
    ) {
        $this->pattern            = $pattern;
        $this->dimension          = $dimension;
        $this->extractionFunction = $extractionFunction;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|array<string,string|int|bool|array<mixed>>>
     */
    public function toArray(): array
    {
        $result = [
            'type'      => 'regex',
            'dimension' => $this->dimension,
            'pattern'   => $this->pattern,
        ];

        if ($this->extractionFunction) {
            $result['extractionFn'] = $this->extractionFunction->toArray();
        }

        return $result;
    }
}