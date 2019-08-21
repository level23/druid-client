<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Exceptions;

use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Exceptions\QueryResponseException;
use Level23\Druid\Queries\TimeSeriesQuery;
use tests\TestCase;

class DruidQueryExceptionTest extends TestCase
{
    public function testException()
    {
        $query = new TimeSeriesQuery('something', new IntervalCollection(), 'all');

        $exception = new QueryResponseException($query->toArray());

        $this->assertEquals($query->toArray(), $exception->getQuery());
    }
}