<?php
declare(strict_types=1);

namespace Level23\Druid\Concerns;

use Level23\Druid\SearchFilters\RegexSearchFilter;
use Level23\Druid\SearchFilters\ContainsSearchFilter;
use Level23\Druid\SearchFilters\FragmentSearchFilter;

trait HasSearchFilters
{
    /**
     * @var \Level23\Druid\SearchFilters\SearchFilterInterface|null
     */
    protected $searchFilter;

    /**
     * @var array
     */
    protected $searchDimensions = [];

    /**
     * Supply the dimensions where we want to search in using a Search Query.
     *
     * @param array $dimensions
     *
     * @return $this
     */
    public function dimensions(array $dimensions)
    {
        $this->searchDimensions = $dimensions;

        return $this;
    }

    /**
     * Only return the dimensions where the dimension contains the value specified in this search query.
     *
     * @param string $value
     * @param bool   $caseSensitive
     *
     * @return $this
     */
    public function searchContains(string $value, bool $caseSensitive = false)
    {
        $this->searchFilter = new ContainsSearchFilter($value, $caseSensitive);

        return $this;
    }

    /**
     * Return the dimensions it contains all of the values specified.
     *
     * @param array|string[] $values
     * @param bool           $caseSensitive
     *
     * @return $this
     */
    public function searchFragment(array $values, bool $caseSensitive = false)
    {
        $this->searchFilter = new FragmentSearchFilter($values, $caseSensitive);

        return $this;
    }

    /**
     * Return the dimension if they match with the given regex pattern.
     *
     * @param string $pattern
     *
     * @return $this
     */
    public function searchRegex(string $pattern)
    {
        $this->searchFilter = new RegexSearchFilter($pattern);

        return $this;
    }
}