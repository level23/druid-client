<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Concerns;

use tests\TestCase;
use Level23\Druid\DruidClient;
use Level23\Druid\Facades\Druid;
use Level23\Druid\Queries\QueryBuilder;

class HasPostAggregationsTest extends TestCase
{
    /**
     * @var \Level23\Druid\Queries\QueryBuilder|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $builder;

    public function setUp(): void
    {
        $client        = new DruidClient([]);
        $this->builder = \Mockery::mock(QueryBuilder::class, [$client, 'dataSource']);
    }


    /**
     * @param string $class
     *
     * @return \Mockery\Generator\MockConfigurationBuilder|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected function getPostAggregationMock(string $class)
    {
        $builder = new Mockery\Generator\MockConfigurationBuilder();
        $builder->setInstanceMock(true);
        $builder->setName($class);
        $builder->addTarget(AggregatorInterface::class);

        return Mockery::mock($builder);
    }

    public function testDivide()
    {
        $this->builder->divide();

    }
}