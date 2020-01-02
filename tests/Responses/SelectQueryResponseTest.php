<?php

declare(strict_types=1);

namespace tests\Level23\Druid\Responses;

use tests\TestCase;
use Level23\Druid\Responses\SelectQueryResponse;

class SelectQueryResponseTest extends TestCase
{
    public function testResponse()
    {
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
                                        ],
                                ],
                        ],
                ],
        ];

        $response = new SelectQueryResponse($rawResponse);

        $this->assertEquals($rawResponse, $response->raw());

        $this->assertEquals($response->pagingIdentifier(), $rawResponse[0]['result']['pagingIdentifiers']);
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
    }

    public function testEmptyResponse()
    {
        $response = new SelectQueryResponse([]);

        $this->assertEquals([], $response->pagingIdentifier());
        $this->assertEquals([], $response->data());
    }
}
