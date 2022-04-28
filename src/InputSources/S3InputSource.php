<?php
declare(strict_types=1);

namespace Level23\Druid\InputSources;

class S3InputSource extends CloudInputSource
{
    /**
     * @var array<string,string>
     */
    protected array $properties;

    /**
     * S3InputSource constructor.
     *
     * @param string[]                    $uris
     * @param string[]                    $prefixes
     * @param array<array<string,string>> $objects
     * @param array<string,string>        $properties
     */
    public function __construct(array $uris = [], array $prefixes = [], array $objects = [], array $properties = [])
    {
        parent::__construct($uris, $prefixes, $objects);

        $this->properties = $properties;
    }

    /**
     * @return array<string,string|string[]|array<array<string,string>>>
     */
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