<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Queries;

use InvalidArgumentException;
use Level23\Druid\Limits\Limit;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Queries\GroupByQuery;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\Aggregations\SumAggregator;
use Level23\Druid\VirtualColumns\VirtualColumn;
use Level23\Druid\Context\GroupByV2QueryContext;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Responses\GroupByQueryResponse;
use Level23\Druid\Collections\DimensionCollection;
use Level23\Druid\Collections\VirtualColumnCollection;
use Level23\Druid\HavingFilters\GreaterThanHavingFilter;
use Level23\Druid\PostAggregations\FieldAccessPostAggregator;

class GroupByQueryTest extends TestCase
{
    /**
     * @testWith ["day"]
     *           ["wrong", true]
     *
     * @param string $granularity
     * @param bool   $expectException
     *
     * @throws \Exception
     */
    public function testQuery(string $granularity, bool $expectException = false)
    {
        if ($expectException) {
            $this->expectException(InvalidArgumentException::class);
        }
        $dimensionCollection = new DimensionCollection(
            new Dimension('column', 'myColumn')
        );

        $intervalCollection = new IntervalCollection(
            new Interval('12-02-2019', '14-02-2019')
        );

        $sum = new SumAggregator('salary', 'myMoney');

        $virtualColumns = new VirtualColumnCollection(
            new VirtualColumn("concat(first_name, ' ', last_name)", 'full_name')
        );

        $subtotals = [['country', 'city'], ['country'], []];

        $context = new GroupByV2QueryContext();
        $context->setFinalize(true);
        $context->setMaxOnDiskStorage(15000);

        $query = new GroupByQuery(
            'tableName',
            $dimensionCollection,
            $intervalCollection,
            [$sum],
            $granularity
        );

        $havingFilter = new GreaterThanHavingFilter('age', 18);

        $filter = new SelectorFilter('name', 'John');

        $limit = new Limit(15, null, 20);

        $fieldAccess = new FieldAccessPostAggregator('field', 'anotherField');

        $query->setFilter($filter);
        $query->setVirtualColumns($virtualColumns);
        $query->setLimit($limit);
        $query->setContext($context);
        $query->setHaving($havingFilter);
        $query->setPostAggregations([$fieldAccess]);
        $query->setSubtotals($subtotals);

        $expected = [
            'queryType'        => 'groupBy',
            'dataSource'       => 'tableName',
            'intervals'        => $intervalCollection->toArray(),
            'dimensions'       => $dimensionCollection->toArray(),
            'granularity'      => $granularity,
            'filter'           => $filter->toArray(),
            'aggregations'     => [$sum->toArray()],
            'virtualColumns'   => $virtualColumns->toArray(),
            'context'          => $context->toArray(),
            'having'           => $havingFilter->toArray(),
            'limitSpec'        => $limit->toArray(),
            'postAggregations' => [$fieldAccess->toArray()],
            'subtotalsSpec'    => $subtotals,
        ];
        $this->assertEquals($expected, $query->toArray());

        $response = $query->parseResponse([['event' => ['name' => 'John']]]);

        $this->assertInstanceOf(GroupByQueryResponse::class, $response);
        $this->assertEquals([['name' => 'John']], $response->data());
        $this->assertEquals([['event' => ['name' => 'John']]], $response->raw());

        $query->setLimit(15);
        $query->setOffset(20);

        $response = $query->toArray();
        $this->assertEquals((new Limit(15, null, 20))->toArray(), $response['limitSpec']);
    }
}
