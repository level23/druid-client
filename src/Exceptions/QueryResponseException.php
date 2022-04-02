<?php
declare(strict_types=1);

namespace Level23\Druid\Exceptions;

use Exception;
use Throwable;

class QueryResponseException extends Exception
{
    protected array $query;

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
