<?php
declare(strict_types=1);

namespace Level23\Druid\InputSources;

use InvalidArgumentException;

class LocalInputSource implements InputSourceInterface
{
    protected ?string $baseDir;

    protected array $files;

    protected ?string $filter;

    public function __construct(array $files = [], ?string $baseDir = null, ?string $filter = null)
    {
        if (empty($baseDir) && count($files) == 0) {
            throw new InvalidArgumentException('You have to specify either $baseDir or $files');
        }

        if (
            (!empty($baseDir) && empty($filter))
            ||
            (empty($baseDir) && !empty($filter))
        ) {
            throw new InvalidArgumentException('You have to specify both $filter and $baseDir to make use of these!');
        }

        $this->baseDir = $baseDir;
        $this->filter  = $filter;
        $this->files   = $files;
    }

    public function toArray(): array
    {
        $response = [
            'type' => 'local',
        ];

        if (count($this->files) > 0) {
            $response['files'] = $this->files;
        }

        if (!empty($this->baseDir)) {
            $response['baseDir'] = $this->baseDir;
        }

        if (!empty($this->filter)) {
            $response['filter'] = $this->filter;
        }

        return $response;
    }
}