<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Concerns;

use DateTime;
use Exception;
use Level23\Druid\DruidClient;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Queries\QueryBuilder;

class HasIntervalsTest extends TestCase
{
    /**
     * @var \Level23\Druid\DruidClient
     */
    protected $client;

    /**
     * @var \Level23\Druid\Queries\QueryBuilder
     */
    protected $builder;

    public function setUp(): void
    {
        $this->builder = new QueryBuilder(new DruidClient([]), 'dataSource');
    }

    /**
     * @throws \Exception
     */
    public function testIntervals(): void
    {
        $start  = new DateTime('2019-01-01 00:00:00');
        $stop   = new DateTime('2019-01-31 23:59:59');
        $result = $this->builder->interval($start, $stop);

        $this->assertEquals($this->builder, $result);

        $this->assertEquals(
            [new Interval($start, $stop)],
            $this->builder->getIntervals()
        );
    }

    /**
     * @throws \Exception
     */
    public function testTimestamps(): void
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
    public function testExceptionInCaseOfInvalidDate(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'DateTime::__construct(): Failed to parse time string (hi) at position 0 (h): The timezone could not be found in the database'
        );

        $this->builder->interval('hi', 'bye');
    }
}
