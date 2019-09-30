<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class TaskResponse extends QueryResponse
{
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
     * or an empty array when no status was found.
     *
     * @return array
     */
    public function data(): array
    {
        return $this->response['status'] ?? [];
    }

    /**
     * Return the task id or an empty string when not known
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->response['status']['id'] ?? '';
    }

    /**
     * Return the status code or an empty string when not known.
     *
     * @return string
     */
    public function getStatusCode(): string
    {
        return $this->response['status']['statusCode'] ?? '';
    }

    /**
     * Return the status or an empty string when not known.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->response['status']['status'] ?? '';
    }

    /**
     * Return the error message or an empty string when not known.
     *
     * @return string
     */
    public function getErrorMsg(): string
    {
        return $this->response['status']['errorMsg'] ?? '';
    }
}