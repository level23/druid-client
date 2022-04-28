<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Queries;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\Queries\TimeSeriesQuery;
use Level23\Druid\Aggregations\SumAggregator;
use Level23\Druid\DataSources\TableDataSource;
use Level23\Druid\VirtualColumns\VirtualColumn;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Context\TimeSeriesQueryContext;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Responses\TimeSeriesQueryResponse;
use Level23\Druid\Collections\VirtualColumnCollection;
use Level23\Druid\Collections\PostAggregationCollection;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;

class TimeSeriesQueryTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testQuery(): void
    {
        $dataSource  = new TableDataSource('buildings');
        $intervals   = new IntervalCollection(new Interval('12-02-2019', '13-02-2019'));
        $granularity = 'week';

        $query = new TimeSeriesQuery($dataSource, $intervals, $granularity);

        $expected = [
            'queryType'   => 'timeseries',
            'dataSource'  => $dataSource->toArray(),
            'descending'  => false,
            'intervals'   => $intervals->toArray(),
            'granularity' => $granularity,
        ];

        $this->assertEquals($expected, $query->toArray());

        $query->setDescending(true);
        $expected['descending'] = true;
        $this->assertEquals($expected, $query->toArray());

        $filter = new SelectorFilter('type', 'tent');
        $query->setFilter($filter);
        $expected['filter'] = $filter->toArray();
        $this->assertEquals($expected, $query->toArray());

        $aggregations = new AggregationCollection(new SumAggregator('counter', 'total'));
        $query->setAggregations($aggregations);
        $expected['aggregations'] = $aggregations->toArray();
        $this->assertEquals($expected, $query->toArray());

        $postAggregations = new PostAggregationCollection(new FieldAccessPostAggregator('field1', 'field2'));
        $query->setPostAggregations($postAggregations);
        $expected['postAggregations'] = $postAggregations->toArray();
        $this->assertEquals($expected, $query->toArray());

        $context = new TimeSeriesQueryContext(['skipEmptyBuckets' => true]);
        $query->setContext($context);
        $expected['context'] = $context->toArray();
        $this->assertEquals($expected, $query->toArray());

        $virtualColumns = new VirtualColumnCollection(new VirtualColumn('concat(foo, bar)', 'fooBar'));
        $query->setVirtualColumns($virtualColumns);
        $expected['virtualColumns'] = $virtualColumns->toArray();
        $this->assertEquals($expected, $query->toArray());

        $query->setLimit(10);
        $expected['limit'] = 10;
        $this->assertEquals($expected, $query->toArray());

        $query->setTimeOutputName('myTime');
        $this->assertEquals('myTime', $this->getProperty($query, 'timeOutputName'));

        $response = [
            [
                'timestamp' => '12-02-2019 00:00:00',
                'result'    => ['fields' => 'here'],
            ],
        ];

        $responseObj = $query->parseResponse($response);

        $this->assertInstanceOf(TimeSeriesQueryResponse::class, $responseObj);
    }
}
