<?php
declare(strict_types=1);

namespace tests\Level23\Druid;

use Level23\Druid\DruidClient;
use Level23\Druid\QueryBuilder;
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
}