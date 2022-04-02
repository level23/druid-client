<?php
declare(strict_types=1);

namespace Level23\Druid\InputSources;

class S3InputSource extends CloudInputSource
{
    protected array $properties;

    /**
     * S3InputSource constructor.
     *
     * @param array $uris
     * @param array $prefixes
     * @param array $objects
     * @param array $properties
     */
    public function __construct(array $uris = [], array $prefixes = [], array $objects = [], array $properties = [])
    {
        parent::__construct($uris, $prefixes, $objects);

        $this->properties = $properties;
    }

    public function toArray(): array
    {
        $response = parent::toArray();

        if (count($this->properties) > 0) {
            $response['properties'] = $this->properties;
        }

        return $response;
    }

    protected function getCloudType(): string
    {
        return 's3';
    }
}