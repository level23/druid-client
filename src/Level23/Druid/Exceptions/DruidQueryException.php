<?php

namespace Level23\Druid\Exceptions;

use Exception;
use Throwable;

class DruidQueryException extends DruidException
{
    protected $druidQuery;

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
