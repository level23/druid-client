<?php
declare(strict_types=1);

namespace Level23\Druid\DataSources;

class LookupDataSource implements DataSourceInterface
{
    protected string $lookupName;

    public function __construct(string $lookupName)
    {
        $this->lookupName = $lookupName;
    }

    /**
     * Return the LookupDataSource so that it can be used in a druid query.
     *
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'type' => 'lookup',
            'name' => $this->lookupName,
        ];
    }
}