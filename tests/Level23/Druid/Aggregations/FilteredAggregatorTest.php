<?php

namespace tests\Level23\Druid\Aggregations;

use Level23\Druid\Aggregations\FilteredAggregator;
use Level23\Druid\Aggregations\SumAggregator;
use Level23\Druid\Filters\InFilter;
use Level23\Druid\Types\DataType;
use tests\TestCase;

class FilteredAggregatorTest extends TestCase
{
    public function testAggregator()
    {
        $filter = new InFilter('member_id', [1, 2, 6, 112]);
        $sum    = new SumAggregator('calls', 'total_calls', DataType::LONG());

        $aggregator = new FilteredAggregator($filter, $sum);
        $this->assertEquals([
            'type'       => 'filtered',
            'filter'     => $filter->getFilter(),
            'aggregator' => $sum->getAggregator(),
        ], $aggregator->getAggregator());

        $this->assertEquals('total_calls', $aggregator->getOutputName());
    }
}