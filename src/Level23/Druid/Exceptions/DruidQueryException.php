<?php

namespace Level23\Druid\Exceptions;

use Exception;
use Throwable;

class DruidQueryException extends DruidException
{
    protected $druidQuery;

    /**
     * DruidQueryException constructor.
     *
     * @param array           $query
     * @param string          $message
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct(array $query = [], string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->druidQuery = $query;
        parent::__construct($message, $code, $previous);
    }

    public function getQuery(): array
    {
        return $this->druidQuery;
    }
}
