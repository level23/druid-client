<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

/**
 * Class JavascriptFilter
 *
 * The JavaScript filter matches a dimension against the specified JavaScript function predicate.
 * The filter matches values for which the function returns true.
 *
 * The function takes a single argument, the dimension value, and returns either true or false.
 *
 * @package Level23\Druid\Filters
 */
class JavascriptFilter implements FilterInterface
{
    protected string $dimension;

    protected string $javascriptFunction;

    /**
     * JavascriptFilter constructor.
     *
     * @param string                   $dimension
     * @param string                   $javascriptFunction
     */
    public function __construct(
        string $dimension,
        string $javascriptFunction
    ) {
        $this->dimension          = $dimension;
        $this->javascriptFunction = $javascriptFunction;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string|array<string,string|int|bool|array<mixed>>>
     */
    public function toArray(): array
    {
        return [
            'type'      => 'javascript',
            'dimension' => $this->dimension,
            'function'  => $this->javascriptFunction,
        ];
    }
}