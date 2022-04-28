<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

/**
 * Class ExpressionFilter
 *
 * The expression filter allows for the implementation of arbitrary conditions, leveraging the Druid expression system.
 *
 * @package Level23\Druid\Filters
 */
class ExpressionFilter implements FilterInterface
{
    protected string $expression;

    /**
     * @param string $expression
     */
    public function __construct(string $expression) {
        $this->expression = $expression;
    }

    /**
     * Return the filter as it can be used in the druid query.
     *
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'type'      => 'expression',
            'expression' => $this->expression
        ];
    }
}