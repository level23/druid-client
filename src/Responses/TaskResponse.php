<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class TaskResponse
{
    /**
     * @var array
     */
    protected $response;

    /**
     * @var array
     */
    protected $rawResponse;


    public function __construct(array $response)
    {
        $this->rawResponse = $response;
        $this->response    = $response['status'] ?? [];
    }

    /**
     * We will return an array like this:
     *
     * [
     *    [id] => index_traffic-conversions-TEST2_2019-03-18T16:26:05.186Z
     *    [type] => index
     *    [createdTime] => 2019-03-18T16:26:05.202Z
     *    [queueInsertionTime] => 1970-01-01T00:00:00.000Z
     *    [statusCode] => SUCCESS
     *    [status] => SUCCESS
     *    [runnerStatusCode] => WAITING
     *    [duration] => 10255
     *    [location] => Array
     *        (
     *            [host] =>
     *            [port] => -1
     *            [tlsPort] => -1
     *        )
     *
     *
     *    [dataSource] => traffic-conversions-TEST2
     *    [errorMsg] =>
     * ]
     *
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    public function getId()
    {
        return $this->response['id'] ?? '';
    }

    public function getStatusCode()
    {
        return $this->response['statusCode'] ?? '';
    }

    public function getStatus()
    {
        return $this->response['status'] ?? '';
    }

    public function getErrorMsg()
    {
        return $this->response['errorMsg'] ?? '';
    }

    /**
     * @return array
     */
    public function getRawResponse(): array
    {
        return $this->rawResponse;
    }
}