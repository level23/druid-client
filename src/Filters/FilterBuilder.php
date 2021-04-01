<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\DruidClient;
use Level23\Druid\Concerns\HasFilter;

class FilterBuilder
{
    use HasFilter;

    public function __construct(DruidClient $client)
    {
        $this->client = $client;
    }
}