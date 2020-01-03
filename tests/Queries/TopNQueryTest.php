<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Queries;

use tests\TestCase;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Queries\TopNQuery;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\Context\TopNQueryContext;
use Level23\Druid\Aggregations\SumAggregator;
use Level23\Druid\Responses\TopNQueryResponse;
use Level23\Druid\VirtualColumns\VirtualColumn;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Collections\AggregationCollection;
use Level23\Druid\Collections\VirtualColumnCollection;
use Level23\Druid\Collections\PostAggregationCollection;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;

class TopNQueryTest extends TestCase
{
    public function testQuery()
    {
        $dataSource  = 'animals';
        $intervals   = new IntervalCollection(new Interval('12-02-2019', '13-02-2019'));
        $granularity = 'week';
        $dimension   = new Dimension('name', 'pet_name');

        $query = new TopNQuery($dataSource, $intervals, $dimension, 5, 'owners', $granularity);

        $expected = [
            'queryType'   => 'topN',
            'dataSource'  => $dataSource,
            'intervals'   => $intervals->toArray(),
            'granularity' => $granularity,
            'dimension'   => $dimension->toArray(),
            'threshold'   => 5,
            'metric'      => [
                'type'   => 'numeric',
                'metric' => 'owners',
            ],
        ];

        $this->assertEquals($expected, $query->toArray());

        //        $query->setDescending(true);
        //        $expected['descending'] = true;
        //        $this->assertEquals($expected, $query->toArray());
        //
        $filter = new SelectorFilter('type_of_animal', '4-legged-with-tail');
        $query->setFilter($filter);
        $expected['filter'] = $filter->toArray();
        $this->assertEquals($expected, $query->toArray());

        $virtualColumns = new VirtualColumnCollection(new VirtualColumn('concat(foo, bar)', 'fooBar'));
        $query->setVirtualColumns($virtualColumns);
        $expected['virtualColumns'] = $virtualColumns->toArray();
        $this->assertEquals($expected, $query->toArray());

        $aggregations = new AggregationCollection(new SumAggregator('ownerCounter', 'owners'));
        $query->setAggregations($aggregations);
        $expected['aggregations'] = $aggregations->toArray();
        $this->assertEquals($expected, $query->toArray());

        $postAggregations = new PostAggregationCollection(new FieldAccessPostAggregator('field1', 'field2'));
        $query->setPostAggregations($postAggregations);
        $expected['postAggregations'] = $postAggregations->toArray();
        $this->assertEquals($expected, $query->toArray());

        $context = new TopNQueryContext(['minTopNThreshold' => 2]);
        $query->setContext($context);
        $expected['context'] = $context->toArray();
        $this->assertEquals($expected, $query->toArray());

        $query->setDescending(false);
        $expected['metric'] = [
            'type'   => 'inverted',
            'metric' => [
                'type'   => 'numeric',
                'metric' => 'owners',
            ],
        ];
        $this->assertEquals($expected, $query->toArray());

        $response = [
            [
                'result' => ['fields' => 'here'],
            ],
        ];

        $responseObj = $query->parseResponse($response);

        $this->assertInstanceOf(TopNQueryResponse::class, $responseObj);
    }
}
