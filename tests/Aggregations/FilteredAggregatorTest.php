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
        $aggregator = new FilteredAggregator(
            new InFilter('member_id', [1, 2, 6, 112]),
            new SumAggregator('calls', 'total_calls', DataType::LONG())
        );

        $this->assertEquals([
            'type'       => 'filtered',
            'filter'     => [
                'type'      => 'in',
                'dimension' => 'member_id',
                'values'    => [1, 2, 6, 112],
            ],
            'aggregator' => [
                'type'      => 'longSum',
                'name'      => 'total_calls',
                'fieldName' => 'calls',
            ],
        ], $aggregator->toArray());
    }
}