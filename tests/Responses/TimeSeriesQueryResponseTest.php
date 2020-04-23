<?php
declare(strict_types=1);

namespace Level23\Druid\Tests\Responses;

use Level23\Druid\Tests\TestCase;
use Level23\Druid\Responses\TimeSeriesQueryResponse;

class TimeSeriesQueryResponseTest extends TestCase
{
    public function testResponse()
    {
        $rawResponse = [
            0 =>
                [
                    'timestamp' => '2015-09-12T00:00:00.000Z',
                    'result'    =>
                        [
                            'deleted' => 1761,
                            'added'   => 32251,
                            'edited'  => 268,
                        ],
                ],
            1 =>
                [
                    'timestamp' => '2015-09-12T01:00:00.000Z',
                    'result'    =>
                        [
                            'deleted' => 16208,
                            'added'   => 200621,
                            'edited'  => 1144,
                        ],
                ],
        ];

        $response = new TimeSeriesQueryResponse($rawResponse, 'time');

        $this->assertEquals($rawResponse, $response->raw());
        $this->assertEquals([
            [
                'time'    => '2015-09-12T00:00:00.000Z',
                'deleted' => 1761,
                'added'   => 32251,
                'edited'  => 268,
            ],
            [
                'time'    => '2015-09-12T01:00:00.000Z',
                'deleted' => 16208,
                'added'   => 200621,
                'edited'  => 1144,
            ],
        ], $response->data());
    }

    public function testEmptyResponse()
    {
        $response = new TimeSeriesQueryResponse([], 'time');

        $this->assertEquals([], $response->raw());
        $this->assertEquals([], $response->data());
    }
}
