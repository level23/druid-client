<?php
declare(strict_types=1);

namespace Level23\Druid\Responses;

class TaskResponse extends QueryResponse
{
    /**
     * @var array<string,array<string,string|int>>
     */
    protected array $response;

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
     * @return array<string,string|int|array<mixed>>
     */
    public function data(): array
    {
        /** @var array<string,string|int|array<mixed>> $data */
        $data = $this->response['status'] ?? [];

        return $data;
    }

    /**
     * Return the task id or an empty string when not known
     *
     * @return string
     */
    public function getId(): string
    {
        if (isset($this->response['status']['id'])) {
            return (string)$this->response['status']['id'];
        }

        return '';
    }

    /**
     * Return the status code or an empty string when not known.
     *
     * @return string
     */
    public function getStatusCode(): string
    {
        if (isset($this->response['status']['statusCode'])) {
            return (string)$this->response['status']['statusCode'];
        }

        return '';
    }

    /**
     * Return the status or an empty string when not known.
     *
     * @return string
     */
    public function getStatus(): string
    {
        if (isset($this->response['status']['status'])) {
            return (string)$this->response['status']['status'];
        }

        return '';
    }

    /**
     * Return the error message or an empty string when not known.
     *
     * @return string
     */
    public function getErrorMsg(): string
    {
        if(isset($this->response['status']['errorMsg'])) {
            return (string) $this->response['status']['errorMsg'];
        }
        return '';
    }
}