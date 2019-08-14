<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Concerns;

use DateTime;
use Level23\Druid\DruidClient;
use Level23\Druid\Exceptions\DruidException;
use Level23\Druid\Interval\Interval;
use Level23\Druid\QueryBuilder;
use Mockery;
use tests\TestCase;

class HasIntervalsTest extends TestCase
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
        $this->builder = Mockery::mock(QueryBuilder::class, [$this->client, 'http://']);
        $this->builder->makePartial();
    }

    /**
     * @throws \Level23\Druid\Exceptions\DruidException
     */
    public function testIntervals()
    {
        $start = new DateTime('2019-01-01 00:00:00');
        $stop  = new DateTime('2019-01-31 23:59:59');
        $this->builder->interval($start, $stop);

        $this->assertEquals(
            [new Interval($start, $stop)],
            $this->builder->getIntervals()
        );
    }

    /**
     * @throws \Level23\Druid\Exceptions\DruidException
     */
    public function testTimestamps()
    {
        $start = new DateTime('2019-01-01 00:00:00');
        $stop  = new DateTime('2019-01-31 23:59:59');
        $this->builder->interval($start->getTimestamp(), $stop->getTimestamp());

        $this->assertEquals(
            [new Interval($start, $stop)],
            $this->builder->getIntervals()
        );
    }

    /**
     * @throws \Level23\Druid\Exceptions\DruidException
     */
    public function testExceptionInCaseOfInvalidDate()
    {
        $this->expectException(DruidException::class);

        $this->builder->interval("hoi", "doei");
    }
}