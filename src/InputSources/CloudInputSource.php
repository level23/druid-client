<?php
declare(strict_types=1);

namespace Level23\Druid\InputSources;

use InvalidArgumentException;

abstract class CloudInputSource implements InputSourceInterface
{
    /**
     * @var array<string>
     */
    protected array $uris;

    /**
     * @var array<string>
     */
    protected array $prefixes;

    /**
     * @var array<array<string,string>>
     */
    protected array $objects;

    /**
     * S3InputSource constructor.
     *
     * @param array<string>               $uris
     * @param array<string>               $prefixes
     * @param array<array<string,string>> $objects
     */
    public function __construct(array $uris = [], array $prefixes = [], array $objects = [])
    {
        $this->uris     = $uris;
        $this->prefixes = $prefixes;
        $this->objects  = $objects;

        if (count($uris) == 0 && count($prefixes) == 0 && count($objects) == 0) {
            throw new InvalidArgumentException('You have to specify either $uris, $prefixes or $objects');
        }
    }

    abstract protected function getCloudType(): string;

    /**
     * @return array<string,string|string[]|array<array<string,string>>>
     */
    public function toArray(): array
    {
        $response = [
            'type' => $this->getCloudType(),
        ];

        if (count($this->uris) > 0) {
            $response['uris'] = $this->uris;
        }

        if (count($this->prefixes) > 0) {
            $response['prefixes'] = $this->prefixes;
        }

        if (count($this->objects) > 0) {
            $response['objects'] = $this->objects;
        }

        return $response;
    }
}