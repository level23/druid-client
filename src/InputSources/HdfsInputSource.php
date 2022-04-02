<?php
declare(strict_types=1);

namespace Level23\Druid\InputSources;

class HdfsInputSource implements InputSourceInterface
{
    /**
     * HDFS paths. Can be either a JSON array or comma-separated string of paths. Wildcards like * are supported in
     * these paths. Empty files located under one of the given paths will be skipped.
     *
     * @var array|string
     */
    protected $paths;

    /**
     * HdfsInputSource constructor.
     *
     * @param array|string $paths HDFS paths. Can be either a JSON array or comma-separated string of paths. Wildcards
     *                            like * are supported in these paths. Empty files located under one of the given paths
     *                            will be skipped.
     */
    public function __construct($paths)
    {
        $this->paths = $paths;
    }

    public function toArray(): array
    {
        return [
            'type' => 'hdfs',
            'paths' => $this->paths,
        ];
    }
}