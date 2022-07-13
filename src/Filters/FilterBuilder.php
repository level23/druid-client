<?php
declare(strict_types=1);

namespace Level23\Druid\Filters;

use Level23\Druid\Concerns\HasFilter;
use Level23\Druid\Queries\QueryBuilder;

class FilterBuilder
{
    /**
     * @var null|\Level23\Druid\Queries\QueryBuilder
     */
    protected ?QueryBuilder $query = null;

    use HasFilter;

    public function __construct(?QueryBuilder $builder = null)
    {
        $this->query = $builder;
    }
}