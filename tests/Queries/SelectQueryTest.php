<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Queries;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Types\Granularity;
use Level23\Druid\Queries\SelectQuery;
use Level23\Druid\Context\QueryContext;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\DataSources\TableDataSource;
use Level23\Druid\Responses\SelectQueryResponse;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Collections\DimensionCollection;

class SelectQueryTest extends TestCase
{
    /**
     * @testWith [[], false]
     *           [["field"], true]
     *
     * @param string[] $metrics
     * @param bool     $descending
     *
     * @throws \Exception
     */
    public function testQuery(array $metrics, bool $descending): void
    {
        $dimensions = new DimensionCollection(
            new Dimension('age'),
            new Dimension('city')
        );

        $intervalCollection = new IntervalCollection(
            new Interval('12-02-2019', '14-02-2019')
        );

        $threshold = 5;

        $dataSource = new TableDataSource('myDataSource');

        $query = new SelectQuery(
            $dataSource,
            $intervalCollection,
            $threshold,
            $dimensions,
            $metrics,
            $descending
        );

        $expected = [
            'queryType'   => 'select',
            'dataSource'  => $dataSource->toArray(),
            'intervals'   => $intervalCollection->toArray(),
            'descending'  => $descending,
            'dimensions'  => $dimensions->toArray(),
            'metrics'     => $metrics,
            'granularity' => Granularity::ALL,
            'pagingSpec'  => [
                'pagingIdentifiers' => null,
                'threshold'         => $threshold,
            ],
        ];

        $this->assertEquals($expected, $query->toArray());

        // Test with context
        $context = new QueryContext();
        $context->setPriority(100);
        $query->setContext($context);

        $expected['context'] = $context->toArray();
        $this->assertEquals($expected, $query->toArray());

        // Test with filter.
        $filter = new SelectorFilter('city', 'Amsterdam');
        $query->setFilter($filter);
        $expected['filter'] = $filter->toArray();
        $this->assertEquals($expected, $query->toArray());

        $query->setGranularity(Granularity::DAY);
        $expected['granularity'] = Granularity::DAY;
        $this->assertEquals($expected, $query->toArray());

        $identifier = [
            'wikipedia_2015-09-12T00:00:00.000Z_2015-09-13T00:00:00.000Z_2019-09-12T14:15:44.694Z' => 9,
        ];
        $query->setPagingIdentifier($identifier);
        $expected['pagingSpec']['pagingIdentifiers'] = $identifier;
        $this->assertEquals($expected, $query->toArray());

        $response = $query->parseResponse([]);

        $this->assertInstanceOf(SelectQueryResponse::class, $response);
    }
}
