<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Queries;

use tests\TestCase;
use Level23\Druid\Queries\ScanQuery;
use Level23\Druid\Interval\Interval;
use Level23\Druid\Filters\SelectorFilter;
use Level23\Druid\Types\OrderByDirection;
use Level23\Druid\Context\ScanQueryContext;
use Level23\Druid\Types\ScanQueryResultFormat;
use Level23\Druid\Responses\ScanQueryResponse;
use Level23\Druid\Collections\IntervalCollection;

class ScanQueryTest extends TestCase
{
    /**
     * @testWith [true]
     *           [false]
     *
     * @param bool $legacy
     *
     * @throws \Exception
     */
    public function testQuery(bool $legacy)
    {
        $intervals = new IntervalCollection(
            new Interval('12-02-2018', '13-02-2018')
        );

        $query = new ScanQuery(
            'wikipedia',
            $intervals
        );

        $expected = [
            'queryType'    => 'scan',
            'dataSource'   => 'wikipedia',
            'intervals'    => $intervals->toArray(),
            'resultFormat' => ScanQueryResultFormat::NORMAL_LIST,
            'columns'      => [],
        ];

        $this->assertEquals($expected, $query->toArray());

        $filter = new SelectorFilter('channel', '#en.wikipedia');
        $query->setFilter($filter);
        $expected['filter'] = $filter->toArray();
        $this->assertEquals($expected, $query->toArray());

        $query->setBatchSize(100);
        $expected['batchSize'] = 100;
        $this->assertEquals($expected, $query->toArray());

        $query->setLimit(500);
        $expected['limit'] = 500;
        $this->assertEquals($expected, $query->toArray());

        $query->setLegacy($legacy);
        $expected['legacy'] = $legacy;
        $this->assertEquals($expected, $query->toArray());

        $context = new ScanQueryContext();
        $context->setMaxRowsQueuedForOrdering(10);
        $query->setContext($context);

        $expected['context'] = $context->toArray();
        $this->assertEquals($expected, $query->toArray());

        $query->setResultFormat(ScanQueryResultFormat::COMPACTED_LIST);
        $expected['resultFormat'] = ScanQueryResultFormat::COMPACTED_LIST;
        $this->assertEquals($expected, $query->toArray());

        $query->setOrder(OrderByDirection::ASC);
        $expected['order'] = OrderByDirection::ASC;
        $this->assertEquals($expected, $query->toArray());

        $columns = ['added', 'delta'];
        $query->setColumns($columns);
        $expected['columns'] = $columns;
        $this->assertEquals($expected, $query->toArray());

        $rawResponse = [
            0 =>
                [
                    'segmentId' => 'wikipedia_2015-09-12T00:00:00.000Z_2015-09-13T00:00:00.000Z_2019-09-12T14:15:44.694Z',
                    'columns'   =>
                        [
                            0 => 'timestamp',
                            1 => '__time',
                            2 => 'channel',
                            3 => 'user',
                            4 => 'deleted',
                            5 => 'added',
                        ],
                    'events'    =>
                        [
                            0 =>
                                [
                                    'timestamp' => '2015-09-12T00:46:58.771Z',
                                    '__time'    => 1442018818771,
                                    'channel'   => '#en.wikipedia',
                                    'user'      => 'GELongstreet',
                                    'deleted'   => 0,
                                    'added'     => 36,
                                ],
                            1 =>
                                [
                                    'timestamp' => '2015-09-12T00:47:00.496Z',
                                    '__time'    => 1442018820496,
                                    'channel'   => '#ca.wikipedia',
                                    'user'      => 'PereBot',
                                    'deleted'   => 0,
                                    'added'     => 17,
                                ],
                        ],
                ],
            1 =>
                [
                    'segmentId' => 'wikipedia_2015-09-12T00:00:00.000Z_2015-09-13T00:00:00.000Z_2019-09-12T14:15:44.694Z',
                    'columns'   =>
                        [
                            0 => 'timestamp',
                            1 => '__time',
                            2 => 'channel',
                            3 => 'user',
                            4 => 'deleted',
                            5 => 'added',
                        ],
                    'events'    =>
                        [
                            0 =>
                                [
                                    'timestamp' => '2015-09-12T00:47:13.987Z',
                                    '__time'    => 1442018833987,
                                    'channel'   => '#vi.wikipedia',
                                    'user'      => 'ThitxongkhoiAWB',
                                    'deleted'   => 0,
                                    'added'     => 18,
                                ],
                            1 =>
                                [
                                    'timestamp' => '2015-09-12T00:47:17.009Z',
                                    '__time'    => 1442018837009,
                                    'channel'   => '#ca.wikipedia',
                                    'user'      => 'Jaumellecha',
                                    'deleted'   => 20,
                                    'added'     => 0,
                                ],
                        ],
                ],
        ];

        $response = $query->parseResponse($rawResponse);

        $this->assertInstanceOf(ScanQueryResponse::class, $response);
        $this->assertEquals([
            [
                'timestamp' => '2015-09-12T00:46:58.771Z',
                '__time'    => 1442018818771,
                'channel'   => '#en.wikipedia',
                'user'      => 'GELongstreet',
                'deleted'   => 0,
                'added'     => 36,
            ],
            [
                'timestamp' => '2015-09-12T00:47:00.496Z',
                '__time'    => 1442018820496,
                'channel'   => '#ca.wikipedia',
                'user'      => 'PereBot',
                'deleted'   => 0,
                'added'     => 17,
            ],
            [
                'timestamp' => '2015-09-12T00:47:13.987Z',
                '__time'    => 1442018833987,
                'channel'   => '#vi.wikipedia',
                'user'      => 'ThitxongkhoiAWB',
                'deleted'   => 0,
                'added'     => 18,
            ],
            [
                'timestamp' => '2015-09-12T00:47:17.009Z',
                '__time'    => 1442018837009,
                'channel'   => '#ca.wikipedia',
                'user'      => 'Jaumellecha',
                'deleted'   => 20,
                'added'     => 0,
            ],
        ], $response->data());

        $this->assertEquals($rawResponse, $response->raw());
    }
}
