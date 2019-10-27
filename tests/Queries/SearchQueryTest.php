<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Queries;

use tests\TestCase;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Types\Granularity;
use Level23\Druid\Types\SortingOrder;
use Level23\Druid\Queries\SearchQuery;
use Level23\Druid\Context\QueryContext;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\Responses\SearchQueryResponse;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\SearchFilters\ContainsSearchFilter;

class SearchQueryTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testQuery()
    {
        $intervals = new IntervalCollection(
            new Interval('12-02-2018', '13-02-2018')
        );

        $searchFilter = new ContainsSearchFilter('john doe');

        $query = new SearchQuery(
            'wikipedia',
            Granularity::DAY,
            $intervals,
            $searchFilter
        );

        $expected = [
            'queryType'   => 'search',
            'dataSource'  => 'wikipedia',
            'granularity' => 'day',
            'intervals'   => $intervals->toArray(),
            'query'       => $searchFilter->toArray(),
            'sort'        => ['type' => SortingOrder::LEXICOGRAPHIC],
        ];

        $this->assertEquals($expected, $query->toArray());

        $query->setSort(SortingOrder::STRLEN);
        $expected['sort']['type'] = SortingOrder::STRLEN;
        $this->assertEquals($expected, $query->toArray());

        $context = new QueryContext();
        $context->setPriority(100);

        $query->setContext($context);
        $expected['context'] = $context->toArray();
        $this->assertEquals($expected, $query->toArray());

        $query->setLimit(10);
        $expected['limit'] = 10;
        $this->assertEquals($expected, $query->toArray());

        $filter = new SelectorFilter('name', 'john');
        $query->setFilter($filter);
        $expected['filter'] = $filter->toArray();
        $this->assertEquals($expected, $query->toArray());

        $dimensions = ['channel', 'namespace'];
        $query->setDimensions($dimensions);
        $expected['searchDimensions'] = $dimensions;
        $this->assertEquals($expected, $query->toArray());

        $rawResponse = [
            0 =>
                [
                    'timestamp' => '2015-09-12T00:00:00.000Z',
                    'result'    =>
                        [
                            0 =>
                                [
                                    'dimension' => 'namespace',
                                    'value'     => 'Wikipedia',
                                    'count'     => 1153,
                                ],
                            1 =>
                                [
                                    'dimension' => 'namespace',
                                    'value'     => 'Wikipedia discusión',
                                    'count'     => 3,
                                ],
                        ],
                ],
        ];

        $response = $query->parseResponse($rawResponse);

        $this->assertInstanceOf(SearchQueryResponse::class, $response);
        $this->assertEquals([
            0 =>
                [
                    'dimension' => 'namespace',
                    'value'     => 'Wikipedia',
                    'count'     => 1153,
                ],
            1 =>
                [
                    'dimension' => 'namespace',
                    'value'     => 'Wikipedia discusión',
                    'count'     => 3,
                ],
        ], $response->data());

        $this->assertEquals($rawResponse, $response->raw());
    }
}