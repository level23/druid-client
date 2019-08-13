<?php

namespace Level23\Druid\Exceptions;

use Level23\Druid\Queries\QueryInterface;
use Throwable;

class DruidQueryException extends DruidException
{
    /**
     * @var \Level23\Druid\Queries\QueryInterface
     */
    protected $druidQuery;

    /**
     * DruidQueryException constructor.
     *
     * @param \Level23\Druid\Queries\QueryInterface $query
     * @param string                                $message
     * @param int                                   $code
     * @param \Throwable|null                       $previous
     */
    public function __construct(QueryInterface $query, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->druidQuery = $query;
        parent::__construct($message, $code, $previous);
    }

    public function getQuery(): QueryInterface
    {
        return $this->druidQuery;
    }
}
