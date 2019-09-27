<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Queries;

use tests\TestCase;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Types\Granularity;
use Level23\Druid\Queries\SelectQuery;
use Level23\Druid\Context\QueryContext;
use Level23\Druid\Dimensions\Dimension;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\Responses\SelectQueryResponse;
use Level23\Druid\Collections\IntervalCollection;
use Level23\Druid\Collections\DimensionCollection;

class SelectQueryTest extends TestCase
{
    /**
     * @testWith [[], false]
     *
     * @param array $metrics
     * @param bool  $descending
     *
     * @throws \Exception
     */
    public function testQuery(array $metrics, bool $descending)
    {
        $dimensions = new DimensionCollection(
            new Dimension('age'),
            new Dimension('city')
        );

        $intervalCollection = new IntervalCollection(
            new Interval('12-02-2019', '14-02-2019')
        );

        $threshold = 5;

        $query = new SelectQuery(
            'myDataSource',
            $intervalCollection,
            $threshold,
            $dimensions,
            $metrics,
            $descending
        );

        $expected = [
            'queryType'   => 'select',
            'dataSource'  => 'myDataSource',
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

        $rawResponse = [
            0 =>
                [
                    'timestamp' => '2015-09-12T00:46:58.771Z',
                    'result'    =>
                        [
                            'pagingIdentifiers' =>
                                [
                                    'wikipedia_2015-09-12T00:00:00.000Z_2015-09-13T00:00:00.000Z_2019-09-12T14:15:44.694Z' => 19,
                                ],
                            'dimensions'        =>
                                [
                                    0 => '__time',
                                    1 => 'deleted',
                                    2 => 'added',
                                    3 => 'channel',
                                    4 => 'user',
                                ],
                            'metrics'           =>
                                [
                                ],
                            'events'            =>
                                [
                                    0 =>
                                        [
                                            'segmentId' => 'wikipedia_2015-09-12T00:00:00.000Z_2015-09-13T00:00:00.000Z_2019-09-12T14:15:44.694Z',
                                            'offset'    => 10,
                                            'event'     =>
                                                [
                                                    'timestamp' => '2015-09-12T00:47:29.913Z',
                                                    '__time'    => 1442018849913,
                                                    'channel'   => '#en.wikipedia',
                                                    'user'      => 'DavidLeighEllis',
                                                    'deleted'   => 0,
                                                    'added'     => 0,
                                                ],
                                        ],
                                    1 =>
                                        [
                                            'segmentId' => 'wikipedia_2015-09-12T00:00:00.000Z_2015-09-13T00:00:00.000Z_2019-09-12T14:15:44.694Z',
                                            'offset'    => 11,
                                            'event'     =>
                                                [
                                                    'timestamp' => '2015-09-12T00:47:33.004Z',
                                                    '__time'    => 1442018853004,
                                                    'channel'   => '#vi.wikipedia',
                                                    'user'      => 'ThitxongkhoiAWB',
                                                    'deleted'   => 0,
                                                    'added'     => 18,
                                                ],
                                        ]
                                ],
                        ],
                ],
        ];

         $response = $query->parseResponse( $rawResponse );

         $this->assertInstanceOf(SelectQueryResponse::class, $response);
         $this->assertEquals($response->getPagingIdentifier(), $rawResponse[0]['result']['pagingIdentifiers']);

         $this->assertEquals([
             [
                 'timestamp' => '2015-09-12T00:47:29.913Z',
                 '__time'    => 1442018849913,
                 'channel'   => '#en.wikipedia',
                 'user'      => 'DavidLeighEllis',
                 'deleted'   => 0,
                 'added'     => 0,
             ],
             [
                 'timestamp' => '2015-09-12T00:47:33.004Z',
                 '__time'    => 1442018853004,
                 'channel'   => '#vi.wikipedia',
                 'user'      => 'ThitxongkhoiAWB',
                 'deleted'   => 0,
                 'added'     => 18,
             ],
         ], $response->data());

        $this->assertEquals($rawResponse, $response->raw());
    }
}