<?php
declare(strict_types=1);

namespace Level23\Druid\SearchFilters;

class FragmentSearchFilter implements SearchFilterInterface
{
    /**
     * @var string[]
     */
    protected array $values;

    protected bool $caseSensitive;

    /**
     * FragmentSearchFilter constructor.
     *
     * @param string[] $values
     * @param bool     $caseSensitive
     */
    public function __construct(array $values, bool $caseSensitive = false)
    {
        $this->values        = $values;
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * Return the search filter so that it can be used in a search query.
     *
     * @return array<string,string|bool|string[]>
     */
    public function toArray(): array
    {
        return [
            'type'           => 'fragment',
            'values'         => $this->values,
            'case_sensitive' => $this->caseSensitive,
        ];
    }
}