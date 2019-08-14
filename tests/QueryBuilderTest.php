<?php
declare(strict_types=1);

namespace tests\Level23\Druid;

use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\DruidClient;
use Level23\Druid\Queries\TimeSeriesQuery;
use Level23\Druid\QueryBuilder;
use Mockery;
use tests\TestCase;

class QueryBuilderTest extends TestCase
{
    /**
     * @var \Level23\Druid\DruidClient|\Mockery\MockInterface|\Mockery\LegacyMockInterface
     */
    protected $client;

    /**
     * @var \Level23\Druid\QueryBuilder|\Mockery\MockInterface|\Mockery\LegacyMockInterface
     */
    protected $builder;

    public function setUp(): void
    {
        $this->client  = Mockery::mock(DruidClient::class, [[]]);
        $this->builder = Mockery::mock(QueryBuilder::class, [$this->client, 'http://']);
        $this->builder->makePartial();
    }

    /**
     * @throws \Level23\Druid\Exceptions\DruidException
     * @throws \Level23\Druid\Exceptions\DruidQueryException
     */
    public function testExecute()
    {
        $context = ['foo' => 'bar'];
        $query   = Mockery::mock(TimeSeriesQuery::class, ['test', new IntervalCollection()]);

        $result = ['result' => 'here'];

        $normalized = ['normalized' => 'result'];

        $this->builder->shouldAllowMockingProtectedMethods()
            ->shouldReceive('buildQueryAutomatic')
            ->with($context)
            ->once()
            ->andReturn($query);

        $this->client->shouldReceive('executeDruidQuery')
            ->once()
            ->with($query)
            ->andReturn($result);

        $query->shouldReceive('parseResponse')
            ->once()
            ->with($result)
            ->andReturn($normalized);

        $response = $this->builder->execute($context);

        $this->assertEquals($normalized, $response);
    }
}