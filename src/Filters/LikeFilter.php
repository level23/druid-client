<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

/**
 * Class LikeFilter
 *
 * Like filters can be used for basic wildcard searches. They are equivalent to the SQL LIKE operator. Special
 * characters supported are "%" (matches any number of characters) and "_" (matches any one character).
 *
 * @package Level23\Druid\Filters
 */
class LikeFilter implements FilterInterface
{
    protected string $dimension;

    protected string $pattern;

    protected string $escapeCharacter;

    /**
     * LikeFilter constructor.
     *
     * @param string                   $dimension               The dimension to filter on
     * @param string                   $pattern                 LIKE pattern, such as "foo%" or "___bar".
     * @param string                   $escapeCharacter         An escape character that can be used to escape special
     *                                                          characters.
     */
    public function __construct(
        string $dimension,
        string $pattern,
        string $escapeCharacter = '\\'
    ) {
        $this->dimension       = $dimension;
        $this->pattern         = $pattern;
        $this->escapeCharacter = $escapeCharacter;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|array<string,string|int|bool|array<mixed>>>
     */
    public function toArray(): array
    {
        return [
            'type'      => 'like',
            'dimension' => $this->dimension,
            'pattern'   => $this->pattern,
            'escape'    => $this->escapeCharacter,
        ];
    }
}