<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\DruidClient;
use Level23\Druid\Concerns\HasFilter;
use Level23\Druid\Queries\QueryBuilder;

class FilterBuilder
{
    /**
     * @var \Level23\Druid\DruidClient
     */
    protected $client;

    /**
     * @var null|\Level23\Druid\Queries\QueryBuilder
     */
    protected $query;

    use HasFilter;

    public function __construct(DruidClient $client, ?QueryBuilder $builder = null)
    {
        $this->client = $client;
        $this->query  = $builder;
    }
}