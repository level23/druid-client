<?php
declare(strict_types=1);

namespace Level23\Druid\DataSources;

interface DataSourceInterface
{
    /**
     * Return the DataSource in a way so that we can use it in a druid query.
     *
     * @return array<string,string|string[]|array<mixed>>
     */
    public function toArray(): array;
}
