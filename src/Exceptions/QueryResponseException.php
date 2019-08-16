<?php

namespace Level23\Druid\Exceptions;

use Throwable;

class QueryResponseException extends \Exception
{
    /**
     * @var array
     */
    protected $query;

    /**
     * DruidQueryException constructor.
     *
     * @param array           $query
     * @param string          $message
     * @param \Throwable|null $previous
     */
    public function __construct(array $query, string $message = "", Throwable $previous = null)
    {
        $this->query = $query;
        parent::__construct($message, 500, $previous);
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }
}
