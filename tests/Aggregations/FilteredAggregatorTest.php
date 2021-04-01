<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Aggregations;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Types\DataType;
use Level23\Druid\Filters\InFilter;
use Level23\Druid\Aggregations\SumAggregator;
use Level23\Druid\Aggregations\FilteredAggregator;

class FilteredAggregatorTest extends TestCase
{
    public function testAggregator(): void
    {
        $aggregator = new FilteredAggregator(
            new InFilter('member_id', [1, 2, 6, 112]),
            new SumAggregator('calls', 'total_calls', DataType::LONG)
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
