<?php
declare(strict_types=1);

namespace tests\Level23\Druid\Responses;

use tests\TestCase;
use Level23\Druid\Responses\TaskResponse;

class TaskResponseTest extends TestCase
{
    public function testEmptyResponse()
    {
        $response = new TaskResponse([]);

        $this->assertEquals([], $response->data());
        $this->assertEquals([], $response->raw());
        $this->assertEquals('', $response->getStatus());
        $this->assertEquals('', $response->getStatusCode());
        $this->assertEquals('', $response->getErrorMsg());
    }

    public function testResponse()
    {
        $rawResponse = [
            'task'   => 'compact_wikipedia_2019-09-26T18:30:14.334Z',
            'status' =>
                [
                    'id'                 => 'compact_wikipedia_2019-09-26T18:30:14.334Z',
                    'type'               => 'compact',
                    'createdTime'        => '2019-09-26T18:30:14.346Z',
                    'queueInsertionTime' => '1970-01-01T00:00:00.000Z',
                    'statusCode'         => 'SUCCESS',
                    'status'             => 'SUCCESS',
                    'runnerStatusCode'   => 'WAITING',
                    'duration'           => 14833,
                    'location'           =>
                        [
                            'host'    => null,
                            'port'    => -1,
                            'tlsPort' => -1,
                        ],
                    'dataSource'         => 'wikipedia',
                    'errorMsg'           => 'SOMETHING HERE',
                ],
        ];
        $response    = new TaskResponse($rawResponse);

        $this->assertEquals($rawResponse['status'], $response->data());
        $this->assertEquals($rawResponse, $response->raw());
        $this->assertEquals($rawResponse['status']['status'], $response->getStatus());
        $this->assertEquals($rawResponse['status']['statusCode'], $response->getStatusCode());
        $this->assertEquals($rawResponse['status']['errorMsg'], $response->getErrorMsg());
        $this->assertEquals($rawResponse['status']['id'], $response->getId());
    }
}
