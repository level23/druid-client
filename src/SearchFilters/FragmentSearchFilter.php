<?php
declare(strict_types=1);

namespace Level23\Druid\SearchFilters;

class FragmentSearchFilter implements SearchFilterInterface
{
    /**
     * @var array
     */
    protected $values;

    /**
     * @var bool
     */
    protected $caseSensitive;

    /**
     * FragmentSearchFilter constructor.
     *
     * @param array $values
     * @param bool  $caseSensitive
     */
    public function __construct(array $values, bool $caseSensitive = false)
    {
        $this->values        = $values;
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
            'type'           => 'fragment',
            'values'         => $this->values,
            'case_sensitive' => $this->caseSensitive,
        ];
    }
}