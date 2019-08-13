<?php
declare(strict_types=1);

namespace tests\Level23\Druid;

use Hamcrest\Core\IsInstanceOf;
use Level23\Druid\Collections\DimensionCollection;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Dimensions\DimensionInterface;
use Level23\Druid\DruidClient;
use Level23\Druid\Extractions\LookupExtraction;
use Level23\Druid\Filters\InFilter;
use Level23\Druid\QueryBuilder;
use Level23\Druid\Types\DataType;
use tests\TestCase;

class QueryBuilderTest extends TestCase
{
    /**
     * @var \Level23\Druid\DruidClient
     */
    protected $client;

    /**
     * @var \Level23\Druid\QueryBuilder|\Mockery\MockInterface|\Mockery\LegacyMockInterface
     */
    protected $builder;

    public function setUp(): void
    {
        $this->client  = new DruidClient([]);
        $this->builder = \Mockery::mock(QueryBuilder::class, [$this->client, 'http://']);
    }

    /**
     * Test the wherein
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWhereIn()
    {
        $in = \Mockery::mock('overload:' . InFilter::class);
        $in->shouldReceive('__construct')
            ->once()
            ->with('country_iso', ['nl', 'be']);

        $this->builder->makePartial();
        $this->builder->shouldReceive('where')
            ->once()
            ->with(new IsInstanceOf(InFilter::class));

        $this->builder->whereIn('country_iso', ['nl', 'be']);
    }


}