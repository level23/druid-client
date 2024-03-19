<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Queries;

use ValueError;
use Level23\Druid\Limits\Limit;
use Level23\Druid\Tests\TestCase;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Types\Granularity;
use Level23\Druid\Queries\GroupByQuery;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Limits\LimitInterface;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\Aggregations\SumAggregator;
use Level23\Druid\DataSources\TableDataSource;
use Level23\Druid\VirtualColumns\VirtualColumn;
use Level23\Druid\Context\GroupByQueryContext;
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
    public function testQuery(string $granularity, bool $expectException = false): void
    {
        if ($expectException) {
            $this->expectException(ValueError::class);
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

        $context = new GroupByQueryContext();
        $context->setFinalize(true);
        $context->setMaxOnDiskStorage(15000);

        $dataSource = new TableDataSource('tableName');

        $query = new GroupByQuery(
            $dataSource,
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
            'dataSource'       => $dataSource->toArray(),
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

    /**
     * @throws \ReflectionException
     */
    public function testSetLimitAsInt(): void
    {
        $query = new GroupByQuery(
            new TableDataSource('wikipedia'),
            new DimensionCollection(),
            new IntervalCollection(),
            [new SumAggregator('salary', 'myMoney')],
            Granularity::DAY
        );

        $query->setLimit(10);

        /** @var LimitInterface $limit */
        $limit = $this->getProperty($query, 'limit');
        $this->assertInstanceOf(LimitInterface::class, $limit);

        $this->assertEquals(10, $limit->getLimit());
    }

    /**
     * @throws \ReflectionException
     */
    public function testSetOffset(): void
    {
        $query = new GroupByQuery(
            new TableDataSource('wikipedia'),
            new DimensionCollection(),
            new IntervalCollection(),
            [new SumAggregator('salary', 'myMoney')],
            Granularity::DAY
        );

        $query->setOffset(20);

        /** @var LimitInterface $limit */
        $limit = $this->getProperty($query, 'limit');
        $this->assertInstanceOf(LimitInterface::class, $limit);

        $this->assertEquals(null, $limit->getLimit());
        $this->assertEquals(20, $limit->getOffset());
    }
}
