<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Responses;

use tests\TestCase;
use Level23\Druid\Responses\TopNQueryResponse;

class TopNQueryResponseTest extends TestCase
{
    public function testResponse()
    {
        $rawResponse = [
            0 =>
                [
                    'timestamp' => '2015-09-12T00:46:58.771Z',
                    'result'    =>
                        [
                            0 =>
                                [
                                    'channel' => '#en.wikipedia',
                                    'edited'  => 11549,
                                ],
                            1 =>
                                [
                                    'channel' => '#vi.wikipedia',
                                    'edited'  => 9747,
                                ],
                        ],
                ],
        ];

        $response = new TopNQueryResponse($rawResponse);

        $this->assertEquals($rawResponse, $response->raw());

        $this->assertEquals([
            [
                'channel' => '#en.wikipedia',
                'edited'  => 11549,
            ],
            [
                'channel' => '#vi.wikipedia',
                'edited'  => 9747,
            ],
        ], $response->data());
    }

    public function testEmptyResponse()
    {
        $rawResponse = [];

        $response = new TopNQueryResponse($rawResponse);

        $this->assertEquals([], $response->raw());
        $this->assertEquals([], $response->data());
    }
}
