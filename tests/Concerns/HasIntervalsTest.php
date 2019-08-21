<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Concerns;

use Mockery;
use DateTime;
use Exception;
use tests\TestCase;
use Level23\Druid\DruidClient;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Queries\QueryBuilder;

class HasIntervalsTest extends TestCase
{
    /**
     * @var \Level23\Druid\DruidClient
     */
    protected $client;

    /**
     * @var \Level23\Druid\Queries\QueryBuilder|\Mockery\MockInterface|\Mockery\LegacyMockInterface
     */
    protected $builder;

    public function setUp(): void
    {
        $this->client  = new DruidClient([]);
        $this->builder = Mockery::mock(QueryBuilder::class, [$this->client, 'http://']);
        $this->builder->makePartial();
    }

    /**
     * @throws \Exception
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
     * @throws \Exception
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
     * @throws \Exception
     */
    public function testExceptionInCaseOfInvalidDate()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'DateTime::__construct(): Failed to parse time string (hi) at position 0 (h): The timezone could not be found in the database'
        );

        $this->builder->interval('hi', 'bye');
    }
}