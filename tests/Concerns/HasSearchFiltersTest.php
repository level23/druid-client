<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Concerns;

use Mockery;
use Level23\Druid\DruidClient;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Queries\QueryBuilder;
use Level23\Druid\SearchFilters\RegexSearchFilter;
use Level23\Druid\SearchFilters\ContainsSearchFilter;
use Level23\Druid\SearchFilters\FragmentSearchFilter;
use Level23\Druid\SearchFilters\SearchFilterInterface;

class HasSearchFiltersTest extends TestCase
{
    /**
     * @var \Level23\Druid\Queries\QueryBuilder|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $builder;

    public function setUp(): void
    {
        $client        = new DruidClient([]);
        $this->builder = Mockery::mock(QueryBuilder::class, [$client, 'http://']);
        $this->builder->makePartial();
    }

    /**
     * @param string $class
     *
     * @return \Mockery\Generator\MockConfigurationBuilder|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected function getSearchFilterMock(string $class)
    {
        $builder = new Mockery\Generator\MockConfigurationBuilder();
        $builder->setInstanceMock(true);
        $builder->setName($class);
        $builder->addTarget(SearchFilterInterface::class);

        return Mockery::mock($builder);
    }

    /**
     * @throws \Exception
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSearchContainsWithDefaults(): void
    {
        $filter = $this->getSearchFilterMock(ContainsSearchFilter::class);
        $filter->shouldReceive('__construct')
            ->once()
            ->with('wikipedia', false);

        $response = $this->builder->searchContains('wikipedia');

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @testWith ["john", true]
     *           ["DoE", false]
     *
     * @param string $value
     * @param bool   $caseSensitive
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSearchContains(string $value, bool $caseSensitive): void
    {
        $filter = $this->getSearchFilterMock(ContainsSearchFilter::class);
        $filter->shouldReceive('__construct')
            ->once()
            ->with($value, $caseSensitive);

        $response = $this->builder->searchContains($value, $caseSensitive);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @throws \Exception
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSearchFragmentWithDefaults(): void
    {
        $fragment = ['John', 'Doe'];

        $filter = $this->getSearchFilterMock(FragmentSearchFilter::class);
        $filter->shouldReceive('__construct')
            ->once()
            ->with($fragment, false);

        $response = $this->builder->searchFragment($fragment);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @testWith [{"0": "john", "1": "Doe"}, true]
     *           [{"0": "USA", "1": "America"}, false]
     *
     * @param array $values
     * @param bool  $caseSensitive
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSearchFragment(array $values, bool $caseSensitive): void
    {
        $filter = $this->getSearchFilterMock(FragmentSearchFilter::class);
        $filter->shouldReceive('__construct')
            ->once()
            ->with($values, $caseSensitive);

        $response = $this->builder->searchFragment($values, $caseSensitive);

        $this->assertEquals($this->builder, $response);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSearchRegex(): void
    {
        $regex  = "^Wiki";
        $filter = $this->getSearchFilterMock(RegexSearchFilter::class);
        $filter->shouldReceive('__construct')
            ->once()
            ->with($regex);

        $response = $this->builder->searchRegex($regex);

        $this->assertEquals($this->builder, $response);
    }
}
