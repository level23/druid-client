<?php
declare(strict_types=1);

namespace Level23\Druid\SearchFilters;

class ContainsSearchFilter implements SearchFilterInterface
{
    protected string $value;

    protected bool $caseSensitive;

    /**
     * ContainsSearchFilter constructor.
     *
     * @param string $value
     * @param bool   $caseSensitive
     */
    public function __construct(string $value, bool $caseSensitive = false)
    {
        $this->value         = $value;
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * Return the search filter so that it can be used in a search query.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type'  => ($this->caseSensitive ? 'contains' : 'insensitive_contains'),
            'value' => $this->value,
        ];
    }
}