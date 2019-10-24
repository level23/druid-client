<?php
declare(strict_types=1);

namespace Level23\Druid\SearchFilters;

class RegexSearchFilter implements SearchFilterInterface
{
    /**
     * @var string
     */
    protected $pattern;

    /**
     * RegexSearchFilter constructor.
     *
     * @param string $pattern
     */
    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * Return the search filter so that it can be used in a search query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'    => 'regex',
            'pattern' => $this->pattern,
        ];
    }
}